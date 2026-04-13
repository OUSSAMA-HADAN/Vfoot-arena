<?php
require 'config.php';

$pdo = db();
$msg = null;

// ══════════════════════════════════════════════════════════════
// ACTIONS POST
// ══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Soumettre un score ────────────────────────────────────
    if ($action === 'submit_score') {
        $matchId   = (int)$_POST['id_match'];
        $userId    = (int)$_POST['id_user'];
        $homeScore = (int)$_POST['home_score'];
        $awayScore = (int)$_POST['away_score'];

        // Insérer la soumission
        $pdo->prepare("
            INSERT INTO submission (home_score, away_score, id_match, id_user)
            VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE home_score=VALUES(home_score), away_score=VALUES(away_score)
        ")->execute([$homeScore, $awayScore, $matchId, $userId]);

        // Vérifier si les deux joueurs ont soumis
        $subs = $pdo->prepare("SELECT * FROM submission WHERE id_match = ?");
        $subs->execute([$matchId]);
        $submissions = $subs->fetchAll();

        if (count($submissions) === 2) {
            $s1 = $submissions[0];
            $s2 = $submissions[1];

            if ($s1['home_score'] === $s2['home_score'] && $s1['away_score'] === $s2['away_score']) {
                // Accord → confirmer automatiquement
                $pdo->prepare("INSERT INTO resultat (home_score, away_score, id_match, confirmed_by) VALUES (?,?,?,NULL)")
                    ->execute([$s1['home_score'], $s1['away_score'], $matchId]);
                $pdo->prepare("UPDATE matche SET statut='confirmed' WHERE id_match=?")->execute([$matchId]);

                // Mettre à jour les stats de groupe si c'est un match de poule
                $match = $pdo->prepare("SELECT * FROM matche WHERE id_match=?");
                $match->execute([$matchId]);
                $match = $match->fetch();

                if ($match['round'] === 'group' && $match['id_groupe']) {
                    updateGroupStandings($pdo, $match, $s1['home_score'], $s1['away_score']);
                }

                $msg = ['ok', 'Scores identiques → match confirmé automatiquement ✓'];
            } else {
                $pdo->prepare("UPDATE matche SET statut='disputed' WHERE id_match=?")->execute([$matchId]);
                $msg = ['warn', "Scores différents ({$s1['home_score']}-{$s1['away_score']} vs {$s2['home_score']}-{$s2['away_score']}) → litige déclaré"];
            }
        } else {
            $msg = ['info', 'Score soumis — en attente de la soumission de l\'autre joueur'];
        }
    }

    // ── Écraser le score (résolution manuelle) ───────────────
    if ($action === 'overwrite') {
        $matchId   = (int)$_POST['id_match'];
        $homeScore = (int)$_POST['home_score'];
        $awayScore = (int)$_POST['away_score'];

        // Supprimer l'ancien résultat s'il existe, puis insérer le bon
        $pdo->prepare("DELETE FROM resultat WHERE id_match=?")->execute([$matchId]);
        $pdo->prepare("INSERT INTO resultat (home_score, away_score, id_match, confirmed_by) VALUES (?,?,?,NULL)")
            ->execute([$homeScore, $awayScore, $matchId]);
        $pdo->prepare("UPDATE matche SET statut='confirmed' WHERE id_match=?")->execute([$matchId]);

        $match = $pdo->prepare("SELECT * FROM matche WHERE id_match=?");
        $match->execute([$matchId]);
        $match = $match->fetch();

        if ($match['round'] === 'group' && $match['id_groupe']) {
            updateGroupStandings($pdo, $match, $homeScore, $awayScore);
        }

        $msg = ['ok', "Score écrasé → $homeScore — $awayScore confirmé ✓"];
    }

    // ── Créer un match knockout (leg 1) ──────────────────────
    if ($action === 'create_knockout') {
        $cupId  = (int)$_POST['id_cup'];
        $round  = $_POST['round'];
        $homeId = (int)$_POST['home_user_id'];
        $awayId = (int)$_POST['away_user_id'];

        $pdo->prepare("
            INSERT INTO matche (round, leg, id_cup, home_user_id, away_user_id)
            VALUES (?,?,?,?,?)
        ")->execute([$round, '1', $cupId, $homeId, $awayId]);

        $msg = ['ok', "Match knockout créé — Leg 1 (aller) ✓"];
    }

    // ── Créer leg 2 (retour) après confirmation leg 1 ────────
    if ($action === 'create_leg2') {
        $leg1Id = (int)$_POST['leg1_id'];
        $leg1   = $pdo->prepare("SELECT * FROM matche WHERE id_match=?");
        $leg1->execute([$leg1Id]);
        $leg1 = $leg1->fetch();

        // Leg 2 : domicile/extérieur inversés, pointe vers leg 1 via first_leg_id
        $pdo->prepare("
            INSERT INTO matche (round, leg, id_cup, home_user_id, away_user_id, first_leg_id)
            VALUES (?,?,?,?,?,?)
        ")->execute([$leg1['round'], '2', $leg1['id_cup'], $leg1['away_user_id'], $leg1['home_user_id'], $leg1Id]);

        $msg = ['ok', 'Leg 2 (retour) créé — domicile/extérieur inversés ✓'];
    }

    // ── Calculer agrégat et créer barrage si nul ─────────────
    if ($action === 'check_aggregate') {
        $leg2Id = (int)$_POST['leg2_id'];

        $leg2 = $pdo->prepare("SELECT * FROM matche WHERE id_match=?");
        $leg2->execute([$leg2Id]);
        $leg2 = $leg2->fetch();

        $r1 = $pdo->prepare("SELECT * FROM resultat WHERE id_match=?");
        $r1->execute([$leg2['first_leg_id']]);
        $r1 = $r1->fetch();

        $r2 = $pdo->prepare("SELECT * FROM resultat WHERE id_match=?");
        $r2->execute([$leg2Id]);
        $r2 = $r2->fetch();

        if ($r1 && $r2) {
            // Leg 1 : home=A, away=B → r1['home_score'] pour A
            // Leg 2 : home=B, away=A → r2['away_score'] pour A
            $scoreA = $r1['home_score'] + $r2['away_score'];
            $scoreB = $r1['away_score'] + $r2['home_score'];

            $leg1 = $pdo->prepare("SELECT * FROM matche WHERE id_match=?");
            $leg1->execute([$leg2['first_leg_id']]);
            $leg1 = $leg1->fetch();

            $userA = $pdo->prepare("SELECT username FROM users WHERE id_user=?");
            $userA->execute([$leg1['home_user_id']]);
            $userA = $userA->fetch()['username'];

            $userB = $pdo->prepare("SELECT username FROM users WHERE id_user=?");
            $userB->execute([$leg1['away_user_id']]);
            $userB = $userB->fetch()['username'];

            if ($scoreA > $scoreB) {
                $msg = ['ok', "Agrégat : $userA $scoreA — $scoreB $userB → $userA est qualifié 🏆"];
            } elseif ($scoreB > $scoreA) {
                $msg = ['ok', "Agrégat : $userA $scoreA — $scoreB $userB → $userB est qualifié 🏆"];
            } else {
                // Nul → créer barrage
                $pdo->prepare("
                    INSERT INTO matche (round, leg, id_cup, home_user_id, away_user_id, first_leg_id)
                    VALUES (?,?,?,?,?,?)
                ")->execute([$leg2['round'], '3', $leg2['id_cup'], $leg1['home_user_id'], $leg1['away_user_id'], $leg2['first_leg_id']]);
                $msg = ['warn', "Agrégat nul : $userA $scoreA — $scoreB $userB → Match barrage créé ⚡"];
            }
        } else {
            $msg = ['warn', 'Les deux matchs ne sont pas encore confirmés'];
        }
    }
}

// ══════════════════════════════════════════════════════════════
// FONCTION : mise à jour classement groupe
// ══════════════════════════════════════════════════════════════
function updateGroupStandings(PDO $pdo, array $match, int $homeScore, int $awayScore): void
{
    $homeId   = $match['home_user_id'];
    $awayId   = $match['away_user_id'];
    $groupeId = $match['id_groupe'];

    if ($homeScore > $awayScore) {
        // Victoire domicile
        $pdo->prepare("UPDATE est_classe_dans SET played=played+1, wins=wins+1, points=points+3, goals_for=goals_for+?, goals_against=goals_against+? WHERE id_user=? AND id_groupe=?")
            ->execute([$homeScore, $awayScore, $homeId, $groupeId]);
        $pdo->prepare("UPDATE est_classe_dans SET played=played+1, losses=losses+1, goals_for=goals_for+?, goals_against=goals_against+? WHERE id_user=? AND id_groupe=?")
            ->execute([$awayScore, $homeScore, $awayId, $groupeId]);
    } elseif ($awayScore > $homeScore) {
        // Victoire extérieur
        $pdo->prepare("UPDATE est_classe_dans SET played=played+1, losses=losses+1, goals_for=goals_for+?, goals_against=goals_against+? WHERE id_user=? AND id_groupe=?")
            ->execute([$homeScore, $awayScore, $homeId, $groupeId]);
        $pdo->prepare("UPDATE est_classe_dans SET played=played+1, wins=wins+1, points=points+3, goals_for=goals_for+?, goals_against=goals_against+? WHERE id_user=? AND id_groupe=?")
            ->execute([$awayScore, $homeScore, $awayId, $groupeId]);
    } else {
        // Nul
        $pdo->prepare("UPDATE est_classe_dans SET played=played+1, draws=draws+1, points=points+1, goals_for=goals_for+?, goals_against=goals_against+? WHERE id_user=? AND id_groupe=?")
            ->execute([$homeScore, $awayScore, $homeId, $groupeId]);
        $pdo->prepare("UPDATE est_classe_dans SET played=played+1, draws=draws+1, points=points+1, goals_for=goals_for+?, goals_against=goals_against+? WHERE id_user=? AND id_groupe=?")
            ->execute([$awayScore, $homeScore, $awayId, $groupeId]);
    }
}

// ══════════════════════════════════════════════════════════════
// DONNÉES
// ══════════════════════════════════════════════════════════════

$groups  = $pdo->query("SELECT g.*, c.titre as cup FROM groupe g JOIN cup c ON g.id_cup=c.id_cup ORDER BY g.nom")->fetchAll();
$cups    = $pdo->query("SELECT * FROM cup")->fetchAll();
$users   = $pdo->query("SELECT * FROM users WHERE role='player'")->fetchAll();

$selectedGroup = (int)($_GET['groupe'] ?? ($groups[0]['id_groupe'] ?? 0));

// Classement du groupe sélectionné
$standings = $pdo->prepare("
    SELECT e.*, u.username
    FROM est_classe_dans e
    JOIN users u ON e.id_user = u.id_user
    WHERE e.id_groupe = ?
    ORDER BY e.points DESC, (e.goals_for - e.goals_against) DESC
");
$standings->execute([$selectedGroup]);
$standings = $standings->fetchAll();

// Matchs du groupe sélectionné avec soumissions
$groupMatches = $pdo->prepare("
    SELECT m.*,
           h.username as home_name, a.username as away_name,
           sh.home_score as sub_home_h, sh.away_score as sub_away_h,
           sa.home_score as sub_home_a, sa.away_score as sub_away_a,
           r.home_score as res_home, r.away_score as res_away
    FROM matche m
    JOIN users h ON m.home_user_id = h.id_user
    JOIN users a ON m.away_user_id = a.id_user
    LEFT JOIN submission sh ON sh.id_match = m.id_match AND sh.id_user = m.home_user_id
    LEFT JOIN submission sa ON sa.id_match = m.id_match AND sa.id_user = m.away_user_id
    LEFT JOIN resultat r ON r.id_match = m.id_match
    WHERE m.id_groupe = ?
    ORDER BY m.leg, m.id_match
");
$groupMatches->execute([$selectedGroup]);
$groupMatches = $groupMatches->fetchAll();

// Matchs knockout
$knockoutMatches = $pdo->prepare("
    SELECT m.*,
           h.username as home_name, a.username as away_name,
           sh.home_score as sub_home_h, sh.away_score as sub_away_h,
           sa.home_score as sub_home_a, sa.away_score as sub_away_a,
           r.home_score as res_home, r.away_score as res_away
    FROM matche m
    JOIN users h ON m.home_user_id = h.id_user
    JOIN users a ON m.away_user_id = a.id_user
    LEFT JOIN submission sh ON sh.id_match = m.id_match AND sh.id_user = m.home_user_id
    LEFT JOIN submission sa ON sa.id_match = m.id_match AND sa.id_user = m.away_user_id
    LEFT JOIN resultat r ON r.id_match = m.id_match
    WHERE m.round != 'group'
    ORDER BY m.id_match DESC
");
$knockoutMatches->execute();
$knockoutMatches = $knockoutMatches->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pipeline — VFoot Arena</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0a0e27; color: #fff; }
        .card  { background: #1a2150; border: 1px solid #2a3170; }
        .card-title, .section-title { color: #c9a84c; }
        .navbar { background: #1a2150 !important; border-bottom: 2px solid #c9a84c; }
        .navbar-brand { color: #c9a84c !important; font-weight: bold; }
        .nav-tabs .nav-link { color: #aaa; border-color: #2a3170; }
        .nav-tabs .nav-link.active { background: #1a2150; color: #c9a84c; border-bottom-color: #1a2150; }
        table { color: #fff; }
        thead { color: #c9a84c; }
        .badge-leg1 { background: #3498db; }
        .badge-leg2 { background: #9b59b6; }
        .badge-leg3 { background: #e74c3c; }
        .score-input { width: 60px; display: inline-block; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">← Dashboard</a>
        <span class="text-muted">Pipeline de test</span>
    </div>
</nav>

<div class="container-fluid px-4">

    <?php if ($msg): ?>
        <?php $type = match($msg[0]) { 'ok'=>'success', 'warn'=>'warning', 'info'=>'info', default=>'danger' }; ?>
        <div class="alert alert-<?= $type ?> alert-dismissible">
            <?= htmlspecialchars($msg[1]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="tabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#groupes">⚽ Phase de groupes</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#knockout">🏆 Knockout</a></li>
    </ul>

    <div class="tab-content">

        <!-- ══════════════════════════════════════════════════ -->
        <!-- TAB GROUPES                                        -->
        <!-- ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="groupes">
            <div class="row g-4">

                <!-- Sélecteur de groupe -->
                <div class="col-12">
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <label class="text-white mb-0">Groupe :</label>
                        <select name="groupe" class="form-select form-select-sm bg-dark text-white border-secondary w-auto" onchange="this.form.submit()">
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id_groupe'] ?>" <?= $g['id_groupe'] == $selectedGroup ? 'selected' : '' ?>>
                                    Groupe <?= $g['nom'] ?> — <?= htmlspecialchars($g['cup']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Classement -->
                <div class="col-md-5">
                    <div class="card p-4">
                        <h5 class="card-title">Classement</h5>
                        <table class="table table-sm table-dark table-hover mb-0">
                            <thead>
                                <tr><th>#</th><th>Joueur</th><th>J</th><th>G</th><th>N</th><th>P</th><th>BP</th><th>BC</th><th>Pts</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($standings as $i => $s): ?>
                                <tr class="<?= $i < 2 ? 'table-success bg-opacity-25' : '' ?>">
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($s['username']) ?></td>
                                    <td><?= $s['played'] ?></td>
                                    <td><?= $s['wins'] ?></td>
                                    <td><?= $s['draws'] ?></td>
                                    <td><?= $s['losses'] ?></td>
                                    <td><?= $s['goals_for'] ?></td>
                                    <td><?= $s['goals_against'] ?></td>
                                    <td><strong style="color:#c9a84c"><?= $s['points'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <small class="text-muted mt-2">🟢 Top 2 qualifiés</small>
                    </div>
                </div>

                <!-- Matchs du groupe -->
                <div class="col-md-7">
                    <div class="card p-4">
                        <h5 class="card-title">Matchs</h5>
                        <?php foreach ($groupMatches as $m): ?>
                        <div class="border border-secondary rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="badge <?= $m['leg']==='1' ? 'badge-leg1' : 'badge-leg2' ?> text-white me-2">
                                        <?= $m['leg']==='1' ? 'Aller' : 'Retour' ?>
                                    </span>
                                    <strong><?= htmlspecialchars($m['home_name']) ?></strong>
                                    <span class="text-muted mx-2">vs</span>
                                    <strong><?= htmlspecialchars($m['away_name']) ?></strong>
                                </div>
                                <span class="badge bg-<?= match($m['statut']) {
                                    'confirmed'=>'success', 'disputed'=>'danger',
                                    'submitted'=>'warning', default=>'secondary'
                                } ?>">
                                    <?= $m['statut'] ?>
                                </span>
                            </div>

                            <?php if ($m['statut'] === 'confirmed'): ?>
                                <div class="text-center">
                                    <span class="fs-4 fw-bold text-success">
                                        <?= $m['res_home'] ?> — <?= $m['res_away'] ?>
                                    </span>
                                    <small class="text-muted ms-2">(confirmé)</small>
                                </div>

                            <?php elseif ($m['statut'] === 'disputed'): ?>
                                <div class="text-warning small mb-2">
                                    ⚠️ Litige — <?= $m['home_name'] ?> a soumis <?= $m['sub_home_h'] ?>-<?= $m['sub_away_h'] ?>,
                                    <?= $m['away_name'] ?> a soumis <?= $m['sub_home_a'] ?>-<?= $m['sub_away_a'] ?>
                                </div>
                                <form method="POST" class="d-flex align-items-center gap-2 flex-wrap">
                                    <input type="hidden" name="action" value="overwrite">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <small class="text-muted">Score final :</small>
                                    <input type="number" name="home_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" placeholder="0" min="0" required>
                                    <span class="text-muted">—</span>
                                    <input type="number" name="away_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" placeholder="0" min="0" required>
                                    <button class="btn btn-danger btn-sm">Écraser ✓</button>
                                </form>

                            <?php else: ?>
                                <!-- Soumission domicile -->
                                <?php if ($m['sub_home_h'] === null): ?>
                                <form method="POST" class="d-flex align-items-center gap-2 mb-2">
                                    <input type="hidden" name="action" value="submit_score">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <input type="hidden" name="id_user" value="<?= $m['home_user_id'] ?>">
                                    <small class="text-muted" style="min-width:80px"><?= htmlspecialchars($m['home_name']) ?> :</small>
                                    <input type="number" name="home_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <span class="text-muted">—</span>
                                    <input type="number" name="away_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <button class="btn btn-outline-primary btn-sm">Soumettre</button>
                                </form>
                                <?php else: ?>
                                <div class="text-muted small mb-1">✅ <?= $m['home_name'] ?> a soumis : <?= $m['sub_home_h'] ?>-<?= $m['sub_away_h'] ?></div>
                                <?php endif; ?>

                                <!-- Soumission extérieur -->
                                <?php if ($m['sub_home_a'] === null): ?>
                                <form method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="action" value="submit_score">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <input type="hidden" name="id_user" value="<?= $m['away_user_id'] ?>">
                                    <small class="text-muted" style="min-width:80px"><?= htmlspecialchars($m['away_name']) ?> :</small>
                                    <input type="number" name="home_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <span class="text-muted">—</span>
                                    <input type="number" name="away_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <button class="btn btn-outline-warning btn-sm">Soumettre</button>
                                </form>
                                <?php else: ?>
                                <div class="text-muted small">✅ <?= $m['away_name'] ?> a soumis : <?= $m['sub_home_a'] ?>-<?= $m['sub_away_a'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!-- TAB KNOCKOUT                                       -->
        <!-- ══════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="knockout">
            <div class="row g-4">

                <!-- Créer un match knockout -->
                <div class="col-md-4">
                    <div class="card p-4">
                        <h5 class="card-title">Créer un match knockout (Leg 1)</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="create_knockout">
                            <div class="mb-2">
                                <label class="form-label small text-white mb-1">Cup</label>
                                <select name="id_cup" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <?php foreach ($cups as $c): ?>
                                        <option value="<?= $c['id_cup'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-white mb-1">Phase</label>
                                <select name="round" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="R16">Huitièmes (R16)</option>
                                    <option value="QF">Quarts (QF)</option>
                                    <option value="SF">Demi-finales (SF)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-white mb-1">Joueur domicile</label>
                                <select name="home_user_id" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white mb-1">Joueur extérieur</label>
                                <select name="away_user_id" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id_user'] ?>" <?= $u === array_key_first($users) ? '' : ($u == $users[1] ? 'selected' : '') ?>><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-warning btn-sm w-100">Créer Leg 1</button>
                        </form>
                    </div>
                </div>

                <!-- Liste matchs knockout -->
                <div class="col-md-8">
                    <?php
                    // Regrouper par first_leg_id pour afficher les duels ensemble
                    $duels = [];
                    foreach ($knockoutMatches as $m) {
                        $key = $m['first_leg_id'] ?? $m['id_match'];
                        $duels[$key][] = $m;
                    }
                    ?>
                    <?php foreach ($duels as $duelKey => $legs): ?>
                    <?php
                        $leg1 = collect_leg($legs, '1') ?? collect_leg($legs, 'single');
                        $leg2 = collect_leg($legs, '2');
                        $leg3 = collect_leg($legs, '3');
                    ?>
                    <div class="card p-4 mb-3">
                        <h6 class="card-title mb-3">
                            <?= $leg1 ? htmlspecialchars($leg1['home_name']) . ' vs ' . htmlspecialchars($leg1['away_name']) : '' ?>
                            <span class="badge bg-secondary ms-2"><?= $leg1['round'] ?? '' ?></span>
                        </h6>

                        <?php foreach ($legs as $m): ?>
                        <div class="border border-secondary rounded p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="badge text-white me-2 <?= match($m['leg']) { '1'=>'badge-leg1','2'=>'badge-leg2','3'=>'badge-leg3',default=>'bg-secondary' } ?>">
                                        <?= match($m['leg']) { '1'=>'Aller','2'=>'Retour','3'=>'Barrage ⚡',default=>'Match unique' } ?>
                                    </span>
                                    <strong><?= htmlspecialchars($m['home_name']) ?></strong>
                                    <span class="text-muted mx-1">vs</span>
                                    <strong><?= htmlspecialchars($m['away_name']) ?></strong>
                                </div>
                                <span class="badge bg-<?= match($m['statut']) { 'confirmed'=>'success','disputed'=>'danger','submitted'=>'warning',default=>'secondary' } ?>">
                                    <?= $m['statut'] ?>
                                </span>
                            </div>

                            <?php if ($m['statut'] === 'confirmed'): ?>
                                <div class="text-center mb-2">
                                    <span class="fs-5 fw-bold text-success"><?= $m['res_home'] ?> — <?= $m['res_away'] ?></span>
                                </div>
                                <!-- Boutons de progression -->
                                <?php if ($m['leg'] === '1' && !$leg2): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_leg2">
                                    <input type="hidden" name="leg1_id" value="<?= $m['id_match'] ?>">
                                    <button class="btn btn-outline-primary btn-sm w-100">Créer Leg 2 (retour) →</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($m['leg'] === '2' && !$leg3): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="check_aggregate">
                                    <input type="hidden" name="leg2_id" value="<?= $m['id_match'] ?>">
                                    <button class="btn btn-warning btn-sm w-100">Calculer agrégat →</button>
                                </form>
                                <?php endif; ?>

                            <?php elseif ($m['statut'] === 'disputed'): ?>
                                <div class="text-warning small mb-2">
                                    ⚠️ Litige — <?= $m['home_name'] ?> a soumis <?= $m['sub_home_h'] ?>-<?= $m['sub_away_h'] ?>,
                                    <?= $m['away_name'] ?> a soumis <?= $m['sub_home_a'] ?>-<?= $m['sub_away_a'] ?>
                                </div>
                                <form method="POST" class="d-flex align-items-center gap-2 flex-wrap">
                                    <input type="hidden" name="action" value="overwrite">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <small class="text-muted">Score final :</small>
                                    <input type="number" name="home_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" placeholder="0" min="0" required>
                                    <span class="text-muted">—</span>
                                    <input type="number" name="away_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" placeholder="0" min="0" required>
                                    <button class="btn btn-danger btn-sm">Écraser ✓</button>
                                </form>

                            <?php else: ?>
                                <?php if ($m['sub_home_h'] === null): ?>
                                <form method="POST" class="d-flex align-items-center gap-2 mb-2">
                                    <input type="hidden" name="action" value="submit_score">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <input type="hidden" name="id_user" value="<?= $m['home_user_id'] ?>">
                                    <small class="text-muted" style="min-width:80px"><?= htmlspecialchars($m['home_name']) ?> :</small>
                                    <input type="number" name="home_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <span>—</span>
                                    <input type="number" name="away_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <button class="btn btn-outline-primary btn-sm">Soumettre</button>
                                </form>
                                <?php else: ?>
                                <div class="text-muted small mb-1">✅ <?= $m['home_name'] ?> : <?= $m['sub_home_h'] ?>-<?= $m['sub_away_h'] ?></div>
                                <?php endif; ?>

                                <?php if ($m['sub_home_a'] === null): ?>
                                <form method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="action" value="submit_score">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <input type="hidden" name="id_user" value="<?= $m['away_user_id'] ?>">
                                    <small class="text-muted" style="min-width:80px"><?= htmlspecialchars($m['away_name']) ?> :</small>
                                    <input type="number" name="home_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <span>—</span>
                                    <input type="number" name="away_score" class="form-control form-control-sm bg-dark text-white border-secondary score-input" value="0" min="0">
                                    <button class="btn btn-outline-warning btn-sm">Soumettre</button>
                                </form>
                                <?php else: ?>
                                <div class="text-muted small">✅ <?= $m['away_name'] ?> : <?= $m['sub_home_a'] ?>-<?= $m['sub_away_a'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($knockoutMatches)): ?>
                        <div class="card p-4 text-center text-muted">Aucun match knockout — créer un match à gauche</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
function collect_leg(array $legs, string $leg): ?array {
    foreach ($legs as $m) {
        if ($m['leg'] === $leg) return $m;
    }
    return null;
}
?>
