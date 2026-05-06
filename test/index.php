<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>VFoot Arena — Tests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0a0e27; color: #fff; }
        .card { background: #1a2150; border: 1px solid #2a3170; }
        .card-title { color: #c9a84c; }
        .navbar { background: #1a2150 !important; border-bottom: 2px solid #c9a84c; }
        .navbar-brand { color: #c9a84c !important; font-weight: bold; }
        .badge-ok { background: #2ecc71; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <span class="navbar-brand">VFoot Arena · Test Dashboard</span>
        <?php
            try { db(); echo '<span class="badge badge-ok text-white">DB connectée ✓</span>'; }
            catch(Exception $e) { echo '<span class="badge bg-danger text-white">DB erreur ✗</span>'; }
        ?>
    </div>
</nav>

<div class="container">
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">👤 Users</h5>
                <p class="text-muted small">Créer, lister, supprimer des utilisateurs</p>
                <a href="users.php" class="btn btn-outline-warning btn-sm">Ouvrir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">🏆 Cups</h5>
                <p class="text-muted small">Créer des cups publiques et privées</p>
                <a href="cups.php" class="btn btn-outline-warning btn-sm">Ouvrir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">👥 Groupes</h5>
                <p class="text-muted small">Créer des groupes pour la phase de poules</p>
                <a href="groups.php" class="btn btn-outline-warning btn-sm">Ouvrir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">⚽ Matchs</h5>
                <p class="text-muted small">Créer des matchs, soumettre des scores</p>
                <a href="matches.php" class="btn btn-outline-warning btn-sm">Ouvrir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="card-title">🔬 Pipeline</h5>
                <p class="text-muted small">Tester le pipeline complet : groupes, scores, knockout, agrégat, barrage</p>
                <a href="pipeline.php" class="btn btn-outline-info btn-sm">Ouvrir</a>
            </div>
        </div>

    </div>

    <div class="mt-4 p-3 rounded" style="border: 1px dashed #c9a84c">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span style="color:#c9a84c" class="fw-bold">🌱 Seed</span>
                <span class="text-muted small ms-2">Remplir la base avec des données de test (remet tout à zéro)</span>
            </div>
            <a href="seed.php" class="btn btn-warning btn-sm" onclick="return confirm('Vider et reremplir la base ?')">Lancer le seed</a>
        </div>
    </div>
</div>

</body>
</html>
