<?php
require 'config.php';

$message = '';

// Créer un user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create') {
        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = db()->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['email'], $_POST['username'], $hash, $_POST['role']]);
        $message = ['type' => 'success', 'text' => "Utilisateur créé ✓"];
    }

    if ($_POST['action'] === 'delete') {
        db()->prepare("DELETE FROM users WHERE id_user = ?")->execute([$_POST['id_user']]);
        $message = ['type' => 'warning', 'text' => "Utilisateur supprimé"];
    }
}

// Lister tous les users
$users = db()->query("SELECT id_user, email, username, role, is_banned, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test — Users</title>
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
        <span class="text-muted">Users</span>
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
                <h5 class="card-title">Créer un utilisateur</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Nom d'utilisateur</label>
                        <input type="text" name="username" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white mb-1">Mot de passe</label>
                        <input type="password" name="password" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white mb-1">Rôle</label>
                        <select name="role" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="player">Player</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button class="btn btn-warning btn-sm w-100">Créer</button>
                </form>
            </div>
        </div>

        <!-- Liste users -->
        <div class="col-md-8">
            <div class="card p-4">
                <h5 class="card-title">Utilisateurs (<?= count($users) ?>)</h5>
                <table class="table table-sm table-dark table-hover">
                    <thead>
                        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Banni</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id_user'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= $u['role'] ?></span></td>
                            <td><?= $u['is_banned'] ? '🔴' : '🟢' ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                                    <button class="btn btn-danger btn-sm py-0" onclick="return confirm('Supprimer ?')">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$users): ?>
                            <tr><td colspan="6" class="text-center text-muted">Aucun utilisateur</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>
