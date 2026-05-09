<?php $this->layout('layout', ['title' => $cup->getTitre()]) ?>

<style>
/* ── Hero ─────────────────────────────────────────────── */
.cup-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #0a0c10 0%, #0d1a10 60%, #0a0c10 100%);
    border: 1px solid rgba(0,196,106,.15);
    border-radius: 16px;
    padding: 2.5rem;
    margin-bottom: 2rem;
}
.cup-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(0,196,106,.12) 0%, transparent 70%);
    pointer-events: none;
}
.cup-hero-title {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -.5px;
    color: #fff;
    margin: 0;
}
.cup-star { color: var(--vf-green); font-size: 1.2rem; }

/* ── Tabs ─────────────────────────────────────────────── */
.vf-tabs { border-bottom: 1px solid var(--vf-border); margin-bottom: 1.75rem; gap: .25rem; display:flex; }
.vf-tab {
    padding: .6rem 1.2rem;
    border-radius: 8px 8px 0 0;
    font-size: .85rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    border: none;
    background: none;
    border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
}
.vf-tab.active { color: var(--vf-green); border-bottom-color: var(--vf-green); }
.vf-tab:hover:not(.active) { color: #e2e8f0; }
.vf-tab-pane { display: none; }
.vf-tab-pane.active { display: block; }

/* ── Bracket ──────────────────────────────────────────── */
.bracket {
    display: flex;
    gap: 0;
    overflow-x: auto;
    padding-bottom: 1rem;
    align-items: center;
}
.bracket-round {
    display: flex;
    flex-direction: column;
    min-width: 200px;
}
.bracket-round-title {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #60a5fa;
    text-align: center;
    margin-bottom: 1rem;
    padding: .3rem .8rem;
    background: rgba(59,130,246,.08);
    border-radius: 20px;
    align-self: center;
}
.bracket-matches {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    flex: 1;
}
.bracket-connector {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    width: 32px;
    flex-shrink: 0;
}
.bracket-connector-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}
.connector-line {
    width: 32px;
    border-top: 1px solid rgba(0,196,106,.25);
}
.connector-vert {
    width: 1px;
    flex: 1;
    background: rgba(0,196,106,.25);
    align-self: flex-end;
    margin-right: 0;
}
.bracket-card {
    background: #151820;
    border: 1px solid var(--vf-border);
    border-radius: 10px;
    padding: .75rem 1rem;
    margin: .4rem 0;
    transition: border-color .2s;
    cursor: pointer;
}
.bracket-card:hover { border-color: rgba(0,196,106,.4); }
.bracket-card.final-card {
    border-color: rgba(0,196,106,.3);
    background: linear-gradient(135deg, #151820, #0d1a10);
    min-width: 220px;
}
.bracket-player {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .2rem 0;
    font-size: .82rem;
}
.bracket-player + .bracket-player {
    border-top: 1px solid var(--vf-border);
    margin-top: .2rem;
    padding-top: .35rem;
}
.bracket-player .name { color: #e2e8f0; font-weight: 600; }
.bracket-player .score {
    font-weight: 700;
    color: var(--vf-green);
    min-width: 20px;
    text-align: right;
}
.bracket-player .score.unknown { color: #334155; }
.bracket-leg-label {
    font-size: .6rem;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: .3rem;
}

/* ── Group tables ─────────────────────────────────────── */
.group-header {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-bottom: .75rem;
}
.group-letter {
    width: 32px; height: 32px;
    background: rgba(0,196,106,.12);
    border: 1px solid rgba(0,196,106,.25);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .9rem;
    color: var(--vf-green);
}
.standing-row { transition: background .15s; }
.standing-row:hover { background: rgba(255,255,255,.03); }
.standing-rank {
    width: 20px;
    font-size: .75rem;
    color: #475569;
    font-weight: 700;
}
.standing-rank.qualified { color: var(--vf-green); }
.qualified-bar {
    width: 3px;
    background: var(--vf-green);
    border-radius: 3px;
    position: absolute;
    left: 0; top: 4px; bottom: 4px;
}

/* ── Participants ──────────────────────────────────────── */
.participant-chip {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem 1rem;
    border-radius: 30px;
    font-size: .82rem;
    font-weight: 600;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--vf-border);
    color: #e2e8f0;
    transition: border-color .2s;
}
.participant-chip:hover { border-color: rgba(0,196,106,.4); }
.participant-chip.organizer {
    background: rgba(59,130,246,.08);
    border-color: rgba(59,130,246,.2);
    color: #60a5fa;
}
.participant-avatar {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--vf-green), #00875a);
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem;
    font-weight: 800;
    color: #000;
    flex-shrink: 0;
}
</style>

<!-- ═══ HERO ═══════════════════════════════════════════════════ -->
<div class="cup-hero mb-4">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-trophy-fill cup-star"></i>
                <span class="vf-section-title" style="color:var(--vf-green)">Tournoi</span>
            </div>
            <h1 class="cup-hero-title mb-2"><?= $this->e($cup->getTitre()) ?></h1>
            <p class="text-secondary mb-0" style="font-size:.9rem">
                Organisé par <strong class="text-white"><?= $this->e($cup->getOrganisateur()) ?></strong>
                &nbsp;·&nbsp;
                <i class="bi bi-people me-1"></i><?= count($participants) ?> / <?= $cup->getMaxPlayers() ?> joueurs
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="vf-badge badge-<?= $this->e($cup->getStatut()) ?>" style="font-size:.85rem;padding:7px 16px">
                <?= $this->e($cup->getStatut()) ?>
            </span>
            <?php if ($cup->getStatut() === 'open'): ?>
                <?php if ($isParticipant): ?>
                    <span class="vf-badge badge-confirmed" style="font-size:.85rem;padding:7px 16px">
                        <i class="bi bi-check-lg me-1"></i>Inscrit
                    </span>
                <?php else: ?>
                    <form action="/cups/<?= $cup->getId() ?>/join" method="POST">
                        <button type="submit" class="btn btn-vf">
                            <i class="bi bi-plus-lg me-1"></i>Rejoindre
                        </button>
                    </form>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>

    <!-- Progress bar -->
    <?php $pct = $cup->getMaxPlayers() > 0 ? min(100, round(count($participants) / $cup->getMaxPlayers() * 100)) : 0; ?>
    <div class="mt-3">
        <div style="height:3px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden">
            <div style="width:<?= $pct ?>%;height:100%;background:var(--vf-green);border-radius:3px;transition:width .6s"></div>
        </div>
    </div>
</div>

<!-- ═══ TABS ════════════════════════════════════════════════════ -->
<div class="vf-tabs">
    <?php if (!empty($knockoutRounds)): ?>
        <button class="vf-tab active" onclick="switchTab('bracket', this)">
            <i class="bi bi-diagram-3 me-1"></i>Bracket
        </button>
    <?php endif ?>
    <?php if (!empty($groups)): ?>
        <button class="vf-tab <?= empty($knockoutRounds) ? 'active' : '' ?>" onclick="switchTab('groups', this)">
            <i class="bi bi-grid me-1"></i>Groupes
        </button>
    <?php endif ?>
    <button class="vf-tab <?= empty($knockoutRounds) && empty($groups) ? 'active' : '' ?>" onclick="switchTab('participants', this)">
        <i class="bi bi-people me-1"></i>Participants
    </button>
</div>

<!-- ═══ TAB : BRACKET ══════════════════════════════════════════ -->
<?php if (!empty($knockoutRounds)): ?>
<div class="vf-tab-pane active" id="tab-bracket">
    <?php
        $roundLabels = ['R16' => 'Seizièmes', 'QF' => 'Quarts', 'SF' => 'Demies', 'F' => 'Finale'];
        $roundOrder  = array_filter(['R16','QF','SF','F'], fn($r) => isset($knockoutRounds[$r]));
    ?>
    <div class="bracket">
        <?php foreach ($roundOrder as $idx => $roundKey):
            $isFinal  = $roundKey === 'F';
            $matches  = $knockoutRounds[$roundKey];
            $leg1s    = array_values(array_filter($matches, fn($m) => $m->getLeg() === '1' || $m->getLeg() === 'single'));
        ?>

        <!-- Round column -->
        <div class="bracket-round">
            <div class="bracket-round-title"><?= $roundLabels[$roundKey] ?></div>
            <div class="bracket-matches">
                <?php foreach ($leg1s as $leg1):
                    $leg2 = null;
                    foreach ($matches as $m) {
                        if ($m->getLeg() === '2'
                            && $m->getHomeName() === $leg1->getAwayName()
                            && $m->getAwayName() === $leg1->getHomeName()) {
                            $leg2 = $m; break;
                        }
                    }
                ?>
                <a href="/matches/<?= $leg1->getId() ?>" class="text-decoration-none">
                    <div class="bracket-card <?= $isFinal ? 'final-card' : '' ?>">

                        <?php if ($isFinal): ?>
                            <div class="bracket-leg-label"><i class="bi bi-trophy-fill me-1" style="color:var(--vf-green)"></i>Finale</div>
                        <?php endif ?>

                        <!-- Leg 1 -->
                        <?php if ($leg2): ?>
                            <div class="bracket-leg-label">Aller</div>
                        <?php endif ?>
                        <div class="bracket-player">
                            <span class="name"><?= $this->e($leg1->getHomeName()) ?></span>
                            <span class="score <?= $leg1->getResultHome() === null ? 'unknown' : '' ?>">
                                <?= $leg1->getResultHome() !== null ? $leg1->getResultHome() : '?' ?>
                            </span>
                        </div>
                        <div class="bracket-player">
                            <span class="name"><?= $this->e($leg1->getAwayName()) ?></span>
                            <span class="score <?= $leg1->getResultAway() === null ? 'unknown' : '' ?>">
                                <?= $leg1->getResultAway() !== null ? $leg1->getResultAway() : '?' ?>
                            </span>
                        </div>

                        <!-- Leg 2 -->
                        <?php if ($leg2): ?>
                        <div class="mt-2 pt-2" style="border-top:1px solid var(--vf-border)">
                            <div class="bracket-leg-label">Retour</div>
                            <div class="bracket-player">
                                <span class="name"><?= $this->e($leg2->getHomeName()) ?></span>
                                <span class="score <?= $leg2->getResultHome() === null ? 'unknown' : '' ?>">
                                    <?= $leg2->getResultHome() !== null ? $leg2->getResultHome() : '?' ?>
                                </span>
                            </div>
                            <div class="bracket-player">
                                <span class="name"><?= $this->e($leg2->getAwayName()) ?></span>
                                <span class="score <?= $leg2->getResultAway() === null ? 'unknown' : '' ?>">
                                    <?= $leg2->getResultAway() !== null ? $leg2->getResultAway() : '?' ?>
                                </span>
                            </div>
                        </div>
                        <?php endif ?>

                    </div>
                </a>
                <?php endforeach ?>
            </div>
        </div>

        <!-- Connector between rounds -->
        <?php if ($idx < count($roundOrder) - 1): ?>
        <div class="d-flex align-items-center" style="width:32px;align-self:stretch">
            <div style="width:100%;border-top:1px solid rgba(0,196,106,.2)"></div>
        </div>
        <?php endif ?>

        <?php endforeach ?>
    </div>
</div>
<?php endif ?>

<!-- ═══ TAB : GROUPES ══════════════════════════════════════════ -->
<?php if (!empty($groups)): ?>
<div class="vf-tab-pane <?= empty($knockoutRounds) ? 'active' : '' ?>" id="tab-groups">
    <div class="row g-3">
        <?php foreach ($groups as $group): ?>
        <div class="col-md-6">
            <div class="vf-card p-4">
                <div class="group-header">
                    <div class="group-letter"><?= $this->e($group->getNom()) ?></div>
                    <span class="fw-bold text-white">Groupe <?= $this->e($group->getNom()) ?></span>
                </div>

                <table class="table vf-table mb-0" style="font-size:.84rem;--bs-table-bg:transparent;--bs-table-striped-bg:transparent;--bs-table-hover-bg:rgba(255,255,255,.03)">
                    <thead>
                        <tr style="background:transparent">
                            <th style="width:20px">#</th>
                            <th>Joueur</th>
                            <th class="text-center">J</th>
                            <th class="text-center">G</th>
                            <th class="text-center">N</th>
                            <th class="text-center">P</th>
                            <th class="text-center">DB</th>
                            <th class="text-center">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($standings[$group->getId()] ?? [] as $i => $s): ?>
                        <tr class="standing-row" style="position:relative;background:transparent">
                            <td>
                                <?php if ($i < $cup->getQualification()): ?>
                                    <div class="qualified-bar"></div>
                                <?php endif ?>
                                <span class="standing-rank <?= $i < $cup->getQualification() ? 'qualified' : '' ?>">
                                    <?= $i + 1 ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-white"><?= $this->e($s->getUsername()) ?></td>
                            <td class="text-center text-secondary"><?= $s->getPlayed() ?></td>
                            <td class="text-center text-secondary"><?= $s->getWins() ?></td>
                            <td class="text-center text-secondary"><?= $s->getDraws() ?></td>
                            <td class="text-center text-secondary"><?= $s->getLosses() ?></td>
                            <td class="text-center text-secondary">
                                <?= $s->getGoalDiff() >= 0 ? '+' . $s->getGoalDiff() : $s->getGoalDiff() ?>
                            </td>
                            <td class="text-center fw-bold" style="color:var(--vf-green)"><?= $s->getPoints() ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <?php if ($cup->getQualification() > 0): ?>
                <div class="mt-2 d-flex align-items-center gap-2">
                    <div style="width:10px;height:2px;background:var(--vf-green);border-radius:2px"></div>
                    <small class="text-secondary" style="font-size:.7rem">
                        Top <?= $cup->getQualification() ?> qualifié<?= $cup->getQualification() > 1 ? 's' : '' ?>
                    </small>
                </div>
                <?php endif ?>
            </div>
        </div>
        <?php endforeach ?>
    </div>
</div>
<?php endif ?>

<!-- ═══ TAB : PARTICIPANTS ══════════════════════════════════════ -->
<div class="vf-tab-pane <?= empty($knockoutRounds) && empty($groups) ? 'active' : '' ?>" id="tab-participants">
    <?php if (empty($participants)): ?>
        <div class="vf-card p-5 text-center">
            <i class="bi bi-people display-4 text-secondary mb-3 d-block"></i>
            <p class="text-secondary mb-0">Aucun participant pour le moment.</p>
        </div>
    <?php else:
        $organizers = array_filter($participants, fn($p) => $p['role'] === 'organizer');
        $players    = array_filter($participants, fn($p) => $p['role'] === 'player');
    ?>

        <!-- Organisateur -->
        <?php if (!empty($organizers)): ?>
        <div class="vf-card p-4 mb-3">
            <p class="vf-section-title mb-3"><i class="bi bi-star-fill me-1" style="color:#60a5fa"></i>Organisateur</p>
            <?php foreach ($organizers as $o): ?>
            <div class="d-flex align-items-center gap-3">
                <div class="participant-avatar" style="width:38px;height:38px;font-size:.85rem;background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                    <?= strtoupper(substr($o['username'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold text-white"><?= $this->e($o['username']) ?></div>
                    <small style="color:#60a5fa;font-size:.75rem">Organisateur</small>
                </div>
            </div>
            <?php endforeach ?>
        </div>
        <?php endif ?>

        <!-- Joueurs -->
        <div class="vf-card">
            <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-2">
                <p class="vf-section-title mb-0"><i class="bi bi-people me-1"></i>Joueurs</p>
                <small class="text-secondary"><?= count($players) ?> / <?= $cup->getMaxPlayers() ?></small>
            </div>
            <table class="table vf-table mb-0" style="--bs-table-bg: transparent; --bs-table-striped-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,.03)">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:40px">#</th>
                        <th>Joueur</th>
                        <th>Date d'inscription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($players as $p): ?>
                    <tr style="background:transparent">
                        <td class="ps-4 text-secondary"><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="participant-avatar" style="width:28px;height:28px;font-size:.7rem">
                                    <?= strtoupper(substr($p['username'], 0, 1)) ?>
                                </div>
                                <span class="fw-semibold text-white"><?= $this->e($p['username']) ?></span>
                            </div>
                        </td>
                        <td class="text-secondary" style="font-size:.83rem">
                            <?= isset($p['date_adhesion']) ? date('d/m/Y', strtotime($p['date_adhesion'])) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.vf-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.vf-tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const pane = document.getElementById('tab-' + name);
    if (pane) pane.classList.add('active');
}
</script>
