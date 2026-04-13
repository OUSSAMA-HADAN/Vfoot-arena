<?php
require 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create') {
        $idGroupe = ($_POST['round'] === 'group' && !empty($_POST['id_groupe'])) ? $_POST['id_groupe'] : null;
        $stmt = db()->prepare("
            INSERT INTO matche (round, leg, id_cup, id_groupe, home_user_id, away_user_id, deadline)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['round'],
            $_POST['leg'],
            $_POST['id_cup'],
            $idGroupe,
            $_POST['home_user_id'],
            $_POST['away_user_id'],
            $_POST['deadline'] ?: null,
        ]);
        $message = ['type' => 'success', 'text' => "Match créé ✓"];
    }

    if ($_POST['action'] === 'delete') {
        db()->prepare("DELETE FROM matche WHERE id_match = ?")->execute([$_POST['id_match']]);
        $message = ['type' => 'warning', 'text' => "Match supprimé"];
    }
}

$matches = db()->query("
    SELECT m.*,
           h.username as home, a.username as away, c.titre as cup
    FROM matche m
    JOIN users h ON m.home_user_id = h.id_user
    JOIN users a ON m.away_user_id = a.id_user
    JOIN cup c   ON m.id_cup = c.id_cup
    ORDER BY m.created_at DESC
")->fetchAll();

$users  = db()->query("SELECT id_user, username FROM users")->fetchAll();
$cups   = db()->query("SELECT id_cup, titre FROM cup")->fetchAll();
$groups = db()->query("SELECT g.id_groupe, g.nom, c.titre as cup FROM groupe g JOIN cup c ON g.id_cup = c.id_cup ORDER BY g.id_cup, g.nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test — Matchs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0a0e27; color: #fff; }
        .card { background: #1a2150; border: 1px solid #2a3170; }
        .card-title { color: #c9a84c; }
        .navbar { background: #1a2150 !important; border-bottom: 2px solid #c9a84c; }
        .navbar-brand { color: #c9a84c !important; font-weight: bold; }
        table { color: #fff; }
        thead { color: #c9a84c; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">← Test Dashboard</a>
        <span class="text-muted">Matchs</span>
    </div>
</nav>

<div class="container">

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?>"><?= $message['text'] ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Formulaire création -->
        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">Créer un match</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Cup</label>
                        <select name="id_cup" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($cups as $c): ?>
                                <option value="<?= $c['id_cup'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Phase</label>
                        <select name="round" id="roundSelect" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="group">Phase de groupes</option>
                            <option value="R16">Huitièmes (R16)</option>
                            <option value="QF">Quarts (QF)</option>
                            <option value="SF">Demi-finales (SF)</option>
                            <option value="F">Finale (F)</option>
                        </select>
                    </div>
                    <div class="mb-2" id="groupeField">
                        <label class="form-label small text-white mb-1">Groupe</label>
                        <select name="id_groupe" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">-- Choisir --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id_groupe'] ?>">
                                    Groupe <?= $g['nom'] ?> — <?= htmlspecialchars($g['cup']) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!$groups): ?>
                                <option disabled>Aucun groupe — créer d'abord dans Groupes</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Manche</label>
                        <select name="leg" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="1">Aller</option>
                            <option value="2">Retour</option>
                            <option value="3">Barrage</option>
                            <option value="single">Match unique</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Joueur domicile</label>
                        <select name="home_user_id" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Joueur extérieur</label>
                        <select name="away_user_id" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white mb-1">Deadline <span class="text-muted">(optionnel)</span></label>
                        <input type="datetime-local" name="deadline" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <button class="btn btn-warning btn-sm w-100">Créer</button>
                </form>
            </div>
        </div>

        <!-- Liste matchs -->
        <div class="col-md-8">
            <div class="card p-4">
                <h5 class="card-title">Matchs (<?= count($matches) ?>)</h5>
                <table class="table table-sm table-dark table-hover">
                    <thead>
                        <tr><th>ID</th><th>Cup</th><th>Round</th><th>Leg</th><th>Domicile</th><th>Extérieur</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $m): ?>
                        <tr>
                            <td><?= $m['id_match'] ?></td>
                            <td><?= htmlspecialchars($m['cup']) ?></td>
                            <td><span class="badge bg-primary"><?= $m['round'] ?></span></td>
                            <td><?= $m['leg'] ?></td>
                            <td><?= htmlspecialchars($m['home']) ?></td>
                            <td><?= htmlspecialchars($m['away']) ?></td>
                            <td><span class="badge bg-secondary"><?= $m['statut'] ?></span></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_match" value="<?= $m['id_match'] ?>">
                                    <button class="btn btn-danger btn-sm py-0" onclick="return confirm('Supprimer ?')">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$matches): ?>
                            <tr><td colspan="8" class="text-center text-muted">Aucun match</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
// Options de manche selon la phase choisie
const legOptions = {
    group: [
        { value: '1', label: 'Aller' },
        { value: '2', label: 'Retour' },
    ],
    R16: [
        { value: '1', label: 'Aller' },
        { value: '2', label: 'Retour' },
        { value: '3', label: 'Barrage' },
    ],
    QF: [
        { value: '1', label: 'Aller' },
        { value: '2', label: 'Retour' },
        { value: '3', label: 'Barrage' },
    ],
    SF: [
        { value: '1', label: 'Aller' },
        { value: '2', label: 'Retour' },
        { value: '3', label: 'Barrage' },
    ],
    F: [
        { value: 'single', label: 'Match unique' },
    ],
};

const roundSelect  = document.getElementById('roundSelect');
const groupeField  = document.getElementById('groupeField');

function updateForm() {
    const round     = roundSelect.value;
    const legSelect = document.querySelector('[name=leg]');
    const options   = legOptions[round] || [];

    // Mettre à jour les options de manche
    legSelect.innerHTML = '';
    options.forEach(opt => {
        const el = document.createElement('option');
        el.value       = opt.value;
        el.textContent = opt.label;
        legSelect.appendChild(el);
    });

    // Afficher le sélecteur de groupe seulement en phase de groupes
    groupeField.style.display = round === 'group' ? 'block' : 'none';
}

roundSelect.addEventListener('change', updateForm);
updateForm(); // initialiser au chargement
</script>

</body>
</html>
