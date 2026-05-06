<?php
require 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create') {
        $stmt = db()->prepare("INSERT INTO groupe (nom, id_cup) VALUES (?, ?)");
        $stmt->execute([$_POST['nom'], $_POST['id_cup']]);
        $message = ['type' => 'success', 'text' => "Groupe créé ✓"];
    }

    if ($_POST['action'] === 'delete') {
        db()->prepare("DELETE FROM groupe WHERE id_groupe = ?")->execute([$_POST['id_groupe']]);
        $message = ['type' => 'warning', 'text' => "Groupe supprimé"];
    }
}

$groups = db()->query("
    SELECT g.*, c.titre as cup
    FROM groupe g
    JOIN cup c ON g.id_cup = c.id_cup
    ORDER BY g.id_cup, g.nom
")->fetchAll();

$cups = db()->query("SELECT id_cup, titre FROM cup")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test — Groupes</title>
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
        <span class="text-muted">Groupes</span>
    </div>
</nav>

<div class="container">

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?>"><?= $message['text'] ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">Créer un groupe</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Lettre du groupe</label>
                        <select name="nom" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <?php foreach (range('A', 'H') as $letter): ?>
                                <option value="<?= $letter ?>">Groupe <?= $letter ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white mb-1">Cup</label>
                        <select name="id_cup" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($cups as $c): ?>
                                <option value="<?= $c['id_cup'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-warning btn-sm w-100">Créer</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-4">
                <h5 class="card-title">Groupes (<?= count($groups) ?>)</h5>
                <table class="table table-sm table-dark table-hover">
                    <thead>
                        <tr><th>ID</th><th>Groupe</th><th>Cup</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g): ?>
                        <tr>
                            <td><?= $g['id_groupe'] ?></td>
                            <td>Groupe <?= $g['nom'] ?></td>
                            <td><?= htmlspecialchars($g['cup']) ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_groupe" value="<?= $g['id_groupe'] ?>">
                                    <button class="btn btn-danger btn-sm py-0" onclick="return confirm('Supprimer ?')">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$groups): ?>
                            <tr><td colspan="4" class="text-center text-muted">Aucun groupe</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>
