<?php
require 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create') {
        $joinCode = $_POST['visibilite'] === 'private' ? $_POST['join_code'] : null;
        $stmt = db()->prepare("
            INSERT INTO cup (titre, visibilite, join_code, min_players, max_players, qualification, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['titre'],
            $_POST['visibilite'],
            $joinCode,
            $_POST['min_players'],
            $_POST['max_players'],
            $_POST['qualification'],
            $_POST['created_by'],
        ]);
        $message = ['type' => 'success', 'text' => "Cup créée ✓"];
    }

    if ($_POST['action'] === 'delete') {
        db()->prepare("DELETE FROM cup WHERE id_cup = ?")->execute([$_POST['id_cup']]);
        $message = ['type' => 'warning', 'text' => "Cup supprimée"];
    }
}

$cups  = db()->query("SELECT c.*, u.username as organisateur FROM cup c JOIN users u ON c.created_by = u.id_user ORDER BY c.created_at DESC")->fetchAll();
$users = db()->query("SELECT id_user, username FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test — Cups</title>
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
        <span class="text-muted">Cups</span>
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
                <h5 class="card-title">Créer une cup</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Titre</label>
                        <input type="text" name="titre" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Visibilité</label>
                        <select name="visibilite" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.join_code.disabled = this.value === 'public'">
                            <option value="public">Publique</option>
                            <option value="private">Privée</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Code d'invitation <span class="text-muted">(privée uniquement)</span></label>
                        <input type="text" name="join_code" class="form-control form-control-sm bg-dark text-white border-secondary" disabled>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <label class="form-label small text-white mb-1">Min joueurs</label>
                            <input type="number" name="min_players" class="form-control form-control-sm bg-dark text-white border-secondary" value="8" min="8">
                        </div>
                        <div class="col">
                            <label class="form-label small text-white mb-1">Max joueurs</label>
                            <input type="number" name="max_players" class="form-control form-control-sm bg-dark text-white border-secondary" value="16" max="64">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Places qualificatives par groupe</label>
                        <input type="number" name="qualification" class="form-control form-control-sm bg-dark text-white border-secondary" value="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white mb-1">Organisateur</label>
                        <select name="created_by" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-warning btn-sm w-100">Créer</button>
                </form>
            </div>
        </div>

        <!-- Liste cups -->
        <div class="col-md-8">
            <div class="card p-4">
                <h5 class="card-title">Cups (<?= count($cups) ?>)</h5>
                <table class="table table-sm table-dark table-hover">
                    <thead>
                        <tr><th>ID</th><th>Titre</th><th>Visibilité</th><th>Statut</th><th>Organisateur</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cups as $c): ?>
                        <tr>
                            <td><?= $c['id_cup'] ?></td>
                            <td><?= htmlspecialchars($c['titre']) ?></td>
                            <td>
                                <span class="badge <?= $c['visibilite'] === 'public' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $c['visibilite'] ?>
                                </span>
                            </td>
                            <td><span class="badge bg-info text-dark"><?= $c['statut'] ?></span></td>
                            <td><?= htmlspecialchars($c['organisateur']) ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_cup" value="<?= $c['id_cup'] ?>">
                                    <button class="btn btn-danger btn-sm py-0" onclick="return confirm('Supprimer ?')">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$cups): ?>
                            <tr><td colspan="6" class="text-center text-muted">Aucune cup</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
document.querySelector('[name=visibilite]').addEventListener('change', function() {
    document.querySelector('[name=join_code]').disabled = this.value === 'public';
});
</script>

</body>
</html>
