<?php $this->layout('layout', ['title' => 'Mes Trophées']) ?>

<?php
    $username = $user?->getUsername() ?: ($_SESSION['user_username'] ?? '?');
    $initials = strtoupper(substr($username, 0, 1));
?>

<style>
    .trophy-card {
        background: var(--vf-card);
        border: 1px solid var(--vf-border);
        border-radius: 14px;
        padding: 1.75rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: border-color .2s, transform .2s;
    }
    .trophy-card:hover {
        border-color: #f59e0b;
        transform: translateY(-2px);
    }
    .trophy-icon {
        width: 56px; height: 56px;
        border-radius: 50%;
        background: rgba(245,158,11,.12);
        border: 2px solid rgba(245,158,11,.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .trophy-name {
        font-size: 1rem;
        font-weight: 700;
        color: #e2e8f0;
    }
    .trophy-date {
        font-size: .78rem;
        color: #64748b;
        margin-top: .2rem;
    }
    .empty-trophy {
        text-align: center;
        padding: 5rem 1rem;
    }
</style>

<!-- Retour profil -->
<div class="mb-4">
    <a href="/profile" class="text-secondary text-decoration-none" style="font-size:.9rem">
        <i class="bi bi-arrow-left me-1"></i>Retour au profil
    </a>
</div>

<!-- Header -->
<div class="vf-card p-4 mb-4 d-flex align-items-center gap-3">
    <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--vf-green),#005f33);
                display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:#fff;flex-shrink:0">
        <?= $initials ?>
    </div>
    <div>
        <h4 class="fw-bold text-white mb-0"><?= $this->e($username) ?></h4>
        <div class="text-secondary" style="font-size:.85rem">
            <?= count($trophees) ?> trophée<?= count($trophees) > 1 ? 's' : '' ?> remporté<?= count($trophees) > 1 ? 's' : '' ?>
        </div>
    </div>
    <div class="ms-auto" style="font-size:2rem">🏆</div>
</div>

<!-- Liste des trophées -->
<?php if (empty($trophees)): ?>
    <div class="vf-card empty-trophy">
        <i class="bi bi-trophy text-secondary" style="font-size:3.5rem"></i>
        <p class="text-secondary mt-4 mb-0 fs-6">
            Aucun trophée pour le moment.<br>
            Gagnez une cup pour décrocher votre premier trophée !
        </p>
        <a href="/cups" class="btn btn-outline-success mt-4">Voir les cups</a>
    </div>
<?php else: ?>
    <div class="d-flex flex-column gap-3">
        <?php foreach ($trophees as $t): ?>
            <div class="trophy-card">
                <div class="trophy-icon">
                    <?php if (!empty($t['icone'])): ?>
                        <img src="<?= $this->e($t['icone']) ?>" alt="" style="width:32px;height:32px;object-fit:contain">
                    <?php else: ?>
                        🏆
                    <?php endif ?>
                </div>
                <div>
                    <div class="trophy-name"><?= $this->e($t['nom']) ?></div>
                    <div class="trophy-date">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d M Y', strtotime($t['date_obtention'])) ?>
                    </div>
                </div>
                <div class="ms-auto">
                    <span style="background:rgba(245,158,11,.12);color:#f59e0b;border:1px solid rgba(245,158,11,.25);
                                 border-radius:20px;padding:3px 12px;font-size:.72rem;font-weight:700;letter-spacing:.5px">
                        CHAMPION
                    </span>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
