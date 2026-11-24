<?php $this->layout('layout', ['title' => 'Mon Profil']) ?>

<?php
    $played    = count($matches);
    $confirmed = array_filter($matches, fn($m) => $m->getStatut() === 'confirmed');
    $pending   = array_filter($matches, fn($m) => $m->getStatut() === 'pending');
    $disputed  = array_filter($matches, fn($m) => $m->getStatut() === 'disputed');
    $tropheeCount ??= 0;

    $username  = $user?->getUsername() ?: ($_SESSION['user_username'] ?? '?');
    $email     = $user?->getEmail()    ?: ($_SESSION['user_email']    ?? '');
    $role      = $user?->getRole()     ?: ($_SESSION['user_role']     ?? 'player');
    $initials  = strtoupper(substr($username, 0, 1));
?>

<style>
    .profile-avatar {
        width: 80px; height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--vf-green), #005f33);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 800; color: #fff;
        flex-shrink: 0;
        border: 3px solid rgba(0,196,106,.3);
    }
    .role-badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: rgba(0,196,106,.12);
        color: var(--vf-green);
        border: 1px solid rgba(0,196,106,.25);
    }
    .role-badge.organizer {
        background: rgba(59,130,246,.12);
        color: #60a5fa;
        border-color: rgba(59,130,246,.25);
    }
    .stat-card {
        background: var(--vf-card);
        border: 1px solid var(--vf-border);
        border-radius: 12px;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: border-color .2s;
    }
    .stat-card:hover { border-color: var(--vf-green); }
    .stat-num { font-size: 2.2rem; font-weight: 800; line-height: 1; }
    .match-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: .85rem 1rem;
        border-radius: 8px;
        background: #0d0f14;
        border: 1px solid var(--vf-border);
        transition: border-color .2s;
        text-decoration: none;
    }
    .match-row:hover { border-color: var(--vf-green); }
    .match-score { font-size: 1rem; font-weight: 700; color: var(--vf-green); }
    .match-teams { font-size: .9rem; font-weight: 600; color: #e2e8f0; }
    .match-meta  { font-size: .78rem; color: #64748b; }
</style>

<!-- Profile hero -->
<div class="vf-card p-4 mb-4 d-flex align-items-center gap-4">
    <div class="profile-avatar"><?= $initials ?></div>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold text-white mb-0"><?= $this->e($username) ?></h3>
            <span class="role-badge <?= $role === 'organizer' ? 'organizer' : '' ?>">
                <?= $role === 'organizer' ? 'Organisateur' : 'Joueur' ?>
            </span>
        </div>
        <div class="text-secondary" style="font-size:.88rem">
            <i class="bi bi-envelope me-1"></i><?= $this->e($email) ?>
        </div>
    </div>
    <div class="text-end d-none d-md-block">
        <div class="vf-section-title mb-1">Membre depuis</div>
        <div class="text-white fw-semibold" style="font-size:.9rem">
            <?php
                $createdAt = $user?->getCreatedAt() ?: '';
                echo $createdAt
                    ? date('d M Y', strtotime($createdAt))
                    : '—';
            ?>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="stat-num text-white"><?= $played ?></div>
            <div class="text-secondary mt-1" style="font-size:.8rem">Matchs joués</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="stat-num" style="color:var(--vf-green)"><?= count($confirmed) ?></div>
            <div class="text-secondary mt-1" style="font-size:.8rem">Confirmés</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="stat-num" style="color:#fbbf24"><?= count($pending) ?></div>
            <div class="text-secondary mt-1" style="font-size:.8rem">En attente</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="stat-num" style="color:#f87171"><?= count($disputed) ?></div>
            <div class="text-secondary mt-1" style="font-size:.8rem">Litiges</div>
        </div>
    </div>
    <div class="col-12 col-md">
        <a href="/profile/trophees" class="stat-card d-block text-decoration-none">
            <div class="stat-num" style="color:#f59e0b"><?= $tropheeCount ?></div>
            <div class="text-secondary mt-1" style="font-size:.8rem">
                <i class="bi bi-trophy-fill me-1" style="color:#f59e0b"></i>Trophées
            </div>
        </a>
    </div>
</div>

<!-- Recent matches -->
<div class="vf-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <p class="vf-section-title mb-0">Derniers matches</p>
        <?php if ($played > 0): ?>
            <small class="text-secondary"><?= $played ?> au total</small>
        <?php endif ?>
    </div>

    <?php if (empty($matches)): ?>
        <div class="text-center py-5">
            <i class="bi bi-controller text-secondary" style="font-size:2.5rem"></i>
            <p class="text-secondary mt-3 mb-0">Aucun match pour le moment.<br>Rejoignez une cup pour commencer !</p>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-2">
            <?php foreach (array_slice($matches, 0, 10) as $m): ?>
                <a href="/matches/<?= $m->getId() ?>" class="match-row">
                    <div>
                        <div class="match-teams">
                            <?= $this->e($m->getHomeName()) ?>
                            <span class="text-secondary mx-1 fw-normal">vs</span>
                            <?= $this->e($m->getAwayName()) ?>
                        </div>
                        <div class="match-meta mt-1">
                            <i class="bi bi-trophy me-1"></i><?= $this->e($m->getCupTitre()) ?>
                            <span class="mx-1">·</span><?= $this->e($m->getRound()) ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($m->getResultHome() !== null): ?>
                            <span class="match-score"><?= $m->getResultHome() ?> – <?= $m->getResultAway() ?></span>
                        <?php else: ?>
                            <span class="text-secondary" style="font-size:.9rem">? – ?</span>
                        <?php endif ?>
                        <span class="vf-badge badge-<?= $this->e($m->getStatut()) ?>">
                            <?= $this->e($m->getStatut()) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
