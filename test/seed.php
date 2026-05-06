<?php
require 'config.php';

$pdo = db();
$log = [];

try {
    $pdo->beginTransaction();

    // ── 0. Vider toutes les tables dans l'ordre (FK oblige) ──────────────
    // DELETE FROM au lieu de TRUNCATE — TRUNCATE provoque un commit implicite en MySQL
    // ce qui casse la transaction et empêche le rollback en cas d'erreur
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ([
        'remporte','stats_joueur','resultat','submission',
        'est_classe_dans','matche','groupe','participe','cup','users','trophee'
    ] as $table) {
        $pdo->exec("DELETE FROM $table");
        $pdo->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $log[] = ['ok', 'Tables vidées'];

    // ── 1. Users ─────────────────────────────────────────────────────────
    $hash = password_hash('password123', PASSWORD_BCRYPT);
    $insertUser = $pdo->prepare("INSERT INTO users (email, username, password, role) VALUES (?,?,?,?)");

    $players = [
        'Mehdi', 'Yassine', 'Amine', 'Omar',
        'Karim', 'Adam', 'Ilyas', 'Sami',
    ];

    $userIds = [];
    foreach ($players as $name) {
        $insertUser->execute([strtolower($name).'@vfoot.com', $name, $hash, 'player']);
        $userIds[$name] = $pdo->lastInsertId();
    }

    // Organisateur
    $insertUser->execute(['orga@vfoot.com', 'Organisateur', $hash, 'player']);
    $orgaId = $pdo->lastInsertId();

    $log[] = ['ok', count($players) . ' joueurs + 1 organisateur créés'];

    // ── 2. Cup ───────────────────────────────────────────────────────────
    $pdo->prepare("
        INSERT INTO cup (titre, visibilite, join_code, min_players, max_players, statut, qualification, created_by)
        VALUES (?,?,?,?,?,?,?,?)
    ")->execute(['Ligue VFoot S1', 'public', null, 8, 16, 'started', 2, $orgaId]);
    $cupId = $pdo->lastInsertId();
    $log[] = ['ok', "Cup #$cupId — Ligue VFoot S1 créée"];

    // ── 3. Participe ─────────────────────────────────────────────────────
    $insertParticipe = $pdo->prepare("INSERT INTO participe (id_user, id_cup, role) VALUES (?,?,'player')");
    foreach ($userIds as $userId) {
        $insertParticipe->execute([$userId, $cupId]);
    }
    $pdo->prepare("INSERT INTO participe (id_user, id_cup, role) VALUES (?,?,'organizer')")->execute([$orgaId, $cupId]);
    $log[] = ['ok', '8 joueurs + 1 organisateur inscrits à la cup'];

    // ── 4. Groupes ───────────────────────────────────────────────────────
    $insertGroupe = $pdo->prepare("INSERT INTO groupe (nom, id_cup) VALUES (?,?)");

    $insertGroupe->execute(['A', $cupId]);
    $groupeA = $pdo->lastInsertId();

    $insertGroupe->execute(['B', $cupId]);
    $groupeB = $pdo->lastInsertId();

    // Groupe A : Mehdi, Yassine, Amine, Omar
    $groupeAPlayers = ['Mehdi', 'Yassine', 'Amine', 'Omar'];
    // Groupe B : Karim, Adam, Ilyas, Sami
    $groupeBPlayers = ['Karim', 'Adam', 'Ilyas', 'Sami'];

    $insertClasse = $pdo->prepare("INSERT INTO est_classe_dans (id_user, id_groupe) VALUES (?,?)");
    foreach ($groupeAPlayers as $name) $insertClasse->execute([$userIds[$name], $groupeA]);
    foreach ($groupeBPlayers as $name) $insertClasse->execute([$userIds[$name], $groupeB]);

    $log[] = ['ok', "Groupe A (A-D) + Groupe B (E-H) créés"];

    // ── 5. Matchs de groupe (aller + retour pour chaque paire) ───────────
    $insertMatch = $pdo->prepare("
        INSERT INTO matche (round, leg, id_cup, id_groupe, home_user_id, away_user_id)
        VALUES ('group', ?, ?, ?, ?, ?)
    ");

    $matchCount = 0;
    $matchIds   = []; // pour stocker les ids des matchs

    foreach ([[$groupeAPlayers, $groupeA], [$groupeBPlayers, $groupeB]] as [$groupPlayers, $groupeId]) {
        // Toutes les paires possibles dans le groupe
        for ($i = 0; $i < count($groupPlayers); $i++) {
            for ($j = $i + 1; $j < count($groupPlayers); $j++) {
                $home = $userIds[$groupPlayers[$i]];
                $away = $userIds[$groupPlayers[$j]];

                // Leg 1 — aller
                $insertMatch->execute(['1', $cupId, $groupeId, $home, $away]);
                $leg1Id = $pdo->lastInsertId();

                // Leg 2 — retour (domicile/extérieur inversés)
                $insertMatch->execute(['2', $cupId, $groupeId, $away, $home]);

                $matchIds[] = $leg1Id;
                $matchCount += 2;
            }
        }
    }

    $log[] = ['ok', "$matchCount matchs de groupe créés (aller + retour)"];

    // ── 6. Soumettre quelques scores pour tester ─────────────────────────
    // On prend les 4 premiers matchs (leg 1) et on soumet des scores
    $insertSub = $pdo->prepare("
        INSERT INTO submission (home_score, away_score, id_match, id_user)
        VALUES (?,?,?,?)
    ");
    $insertResult = $pdo->prepare("
        INSERT INTO resultat (home_score, away_score, id_match, confirmed_by)
        VALUES (?,?,?,NULL)
    ");
    $updateMatch = $pdo->prepare("UPDATE matche SET statut='confirmed' WHERE id_match=?");

    // Match 1 : Mehdi 2 - 1 Yassine (accord automatique)
    $m1 = $matchIds[0];
    $insertSub->execute([2, 1, $m1, $userIds['Mehdi']]);
    $insertSub->execute([2, 1, $m1, $userIds['Yassine']]);
    $insertResult->execute([2, 1, $m1]);
    $updateMatch->execute([$m1]);

    // Match 2 : Amine 0 - 0 Omar (nul, accord automatique)
    $m2 = $matchIds[2];
    $insertSub->execute([0, 0, $m2, $userIds['Amine']]);
    $insertSub->execute([0, 0, $m2, $userIds['Omar']]);
    $insertResult->execute([0, 0, $m2]);
    $updateMatch->execute([$m2]);

    // Match 3 : soumission en litige (scores différents)
    $m3 = $matchIds[4];
    $insertSub->execute([3, 1, $m3, $userIds['Mehdi']]);
    $insertSub->execute([2, 1, $m3, $userIds['Amine']]);
    $pdo->prepare("UPDATE matche SET statut='disputed' WHERE id_match=?")->execute([$m3]);

    $log[] = ['ok', '3 matchs avec scores soumis (2 confirmés, 1 litige)'];

    // ── 7. Trophées ──────────────────────────────────────────────────────
    $insertTrophee = $pdo->prepare("INSERT INTO trophee (nom, icone) VALUES (?,?)");
    $insertTrophee->execute(['Vainqueur de Cup', '🏆']);
    $insertTrophee->execute(['Champion de Groupe', '🥇']);
    $insertTrophee->execute(['Meilleur Buteur', '⚽']);
    $log[] = ['ok', '3 trophées créés'];

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $log[] = ['error', 'ERREUR : ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Seed — VFoot Arena</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0a0e27; color: #fff; }
        .card { background: #1a2150; border: 1px solid #2a3170; }
        .navbar { background: #1a2150 !important; border-bottom: 2px solid #c9a84c; }
        .navbar-brand { color: #c9a84c !important; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">← Test Dashboard</a>
        <span class="text-muted">Seed</span>
    </div>
</nav>

<div class="container" style="max-width:600px">
    <div class="card p-4">
        <h5 class="mb-4" style="color:#c9a84c">Résultat du seeding</h5>
        <?php foreach ($log as [$type, $msg]): ?>
            <div class="d-flex align-items-center mb-2">
                <span class="me-2"><?= $type === 'ok' ? '✅' : '❌' ?></span>
                <span><?= htmlspecialchars($msg) ?></span>
            </div>
        <?php endforeach; ?>

        <?php if ($log[array_key_last($log)][0] === 'ok'): ?>
        <hr class="border-secondary mt-4">
        <p class="small text-muted mb-1">Mot de passe de tous les comptes :</p>
        <code class="text-warning">password123</code>
        <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="users.php"   class="btn btn-outline-warning btn-sm">Voir Users</a>
            <a href="cups.php"    class="btn btn-outline-warning btn-sm">Voir Cups</a>
            <a href="groups.php"  class="btn btn-outline-warning btn-sm">Voir Groupes</a>
            <a href="matches.php" class="btn btn-outline-warning btn-sm">Voir Matchs</a>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
