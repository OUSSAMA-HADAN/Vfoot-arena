<?php

/**
 * VFoot Arena — Seed complet (v2)
 *
 * 3 cups :
 *   Cup 1 "Liga VFoot S1"   — started, groupes en cours
 *   Cup 2 "Arena Cup"       — started, QF en cours (groupes terminés)
 *   Cup 3 "VFoot Masters"   — finished, Zizou champion
 *
 * Mot de passe universel : 00000000
 * Exécuter : php database/seed.php
 */

require_once __DIR__ . '/../php/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../php');
$dotenv->load();

$pdo = new PDO(
    'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4',
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

// ============================================================
// VIDER LES TABLES (ordre FK)
// ============================================================
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach ([
    'remporte', 'trophee', 'stats_joueur', 'resultat',
    'submission', 'est_classe_dans', 'matche',
    'participe', 'groupe', 'cup', 'users',
] as $table) {
    $pdo->exec("TRUNCATE TABLE `$table`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "Tables vidées.\n";

// ============================================================
// USERS  (ids 1..13)
// ============================================================
$hash = password_hash('00000000', PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (email, password, username, id_role) VALUES (?,?,?,?)");
$users = [
    //  email                    hash   username       role
    ['admin@vfoot.com',        $hash, 'VFootAdmin',  2], // id 1 — admin
    ['zizou@vfoot.com',        $hash, 'Zizou',       1], // id 2
    ['cr7@vfoot.com',          $hash, 'CR7',         1], // id 3
    ['messi@vfoot.com',        $hash, 'Messi',       1], // id 4
    ['neymar@vfoot.com',       $hash, 'Neymar',      1], // id 5
    ['mbappe@vfoot.com',       $hash, 'Mbappe',      1], // id 6
    ['salah@vfoot.com',        $hash, 'Salah',       1], // id 7
    ['benzema@vfoot.com',      $hash, 'Benzema',     1], // id 8
    ['haaland@vfoot.com',      $hash, 'Haaland',     1], // id 9
    ['vinicius@vfoot.com',     $hash, 'Vinicius',    1], // id 10
    ['bellingham@vfoot.com',   $hash, 'Bellingham',  1], // id 11
    ['debruyne@vfoot.com',     $hash, 'DeBruyne',    1], // id 12
    ['kane@vfoot.com',         $hash, 'Kane',        1], // id 13
];
foreach ($users as $u) {
    $stmt->execute($u);
}
echo "Utilisateurs insérés (13).\n";

// ============================================================
// CUPS  (ids 1..3)
// ============================================================
$pdo->exec("
    INSERT INTO cup (titre, visibilite, join_code, min_players, max_players, statut, qualification, created_by) VALUES
    ('Liga VFoot S1', 'public', NULL, 8, 16, 'started',  2, 2),
    ('Arena Cup',     'public', NULL, 8, 16, 'started',  2, 3),
    ('VFoot Masters', 'public', NULL, 8, 16, 'finished', 2, 6)
");
// id_cup 1 = Liga VFoot S1  — créée par Zizou(2)
// id_cup 2 = Arena Cup      — créée par CR7(3)
// id_cup 3 = VFoot Masters  — créée par Mbappe(6)
echo "Cups insérées (3).\n";

// ============================================================
// PARTICIPE
// ============================================================
$pdo->exec("
    INSERT INTO participe (id_user, id_cup, role) VALUES
    -- Cup 1 : Zizou organise, 8 joueurs
    (2,  1, 'organizer'),
    (3,  1, 'player'), (4,  1, 'player'), (5,  1, 'player'), (6, 1, 'player'),
    (7,  1, 'player'), (8,  1, 'player'), (9,  1, 'player'),
    -- Cup 2 : CR7 organise, 11 autres joueurs
    (3,  2, 'organizer'),
    (2,  2, 'player'), (4,  2, 'player'), (5,  2, 'player'), (6,  2, 'player'),
    (7,  2, 'player'), (8,  2, 'player'), (9,  2, 'player'), (10, 2, 'player'),
    (11, 2, 'player'), (12, 2, 'player'), (13, 2, 'player'),
    -- Cup 3 : Mbappe organise, 7 autres joueurs
    (6,  3, 'organizer'),
    (2,  3, 'player'), (3,  3, 'player'), (5,  3, 'player'),
    (7,  3, 'player'), (8,  3, 'player'), (9,  3, 'player'), (13, 3, 'player')
");
echo "Participants insérés.\n";

// ============================================================
// GROUPES  (ids 1..8)
// ============================================================
$pdo->exec("
    INSERT INTO groupe (nom, id_cup) VALUES
    ('A', 1), ('B', 1),                     -- ids 1, 2  — Liga VFoot S1
    ('A', 2), ('B', 2), ('C', 2), ('D', 2), -- ids 3,4,5,6 — Arena Cup
    ('E', 3), ('F', 3)                       -- ids 7, 8  — VFoot Masters
");
echo "Groupes insérés (8).\n";

// ============================================================
// MATCHS  (ids 1..43)
// leg='1' pour tous les matchs de groupes (aller uniquement)
// leg='1' pour QF/SF (premier match), leg='single' pour Finale
// ============================================================
$pdo->exec("
    INSERT INTO matche (round, leg, statut, id_cup, id_groupe, home_user_id, away_user_id) VALUES

    -- ── CUP 1 — Liga VFoot S1 ──────────────────────────────────
    -- Groupe A (id=1) : Zizou(2) CR7(3) Messi(4) Neymar(5)
    ('group','1','confirmed', 1,1, 2, 3),  -- m1  Zizou  2-1 CR7   ✓
    ('group','1','confirmed', 1,1, 4, 5),  -- m2  Messi  3-1 Neymar ✓
    ('group','1','pending',   1,1, 2, 4),  -- m3  Zizou vs Messi  (pas joué)
    ('group','1','pending',   1,1, 3, 5),  -- m4  CR7 vs Neymar   (CR7 a soumis)
    ('group','1','pending',   1,1, 2, 5),  -- m5  Zizou vs Neymar
    ('group','1','pending',   1,1, 3, 4),  -- m6  CR7 vs Messi

    -- Groupe B (id=2) : Mbappe(6) Salah(7) Benzema(8) Haaland(9)
    ('group','1','confirmed', 1,2, 6, 7),  -- m7  Mbappe  3-0 Salah   ✓
    ('group','1','confirmed', 1,2, 8, 9),  -- m8  Benzema 2-2 Haaland ✓
    ('group','1','pending',   1,2, 6, 8),  -- m9  Mbappe vs Benzema
    ('group','1','disputed',  1,2, 7, 9),  -- m10 Salah vs Haaland — litige
    ('group','1','pending',   1,2, 6, 9),  -- m11 Mbappe vs Haaland
    ('group','1','pending',   1,2, 7, 8),  -- m12 Salah vs Benzema

    -- ── CUP 2 — Arena Cup ──────────────────────────────────────
    -- Groupe A (id=3) : Zizou(2) DeBruyne(12) Salah(7)
    ('group','1','confirmed', 2,3, 2,12),  -- m13 Zizou    2-0 DeBruyne ✓
    ('group','1','confirmed', 2,3, 2, 7),  -- m14 Zizou    3-1 Salah    ✓
    ('group','1','confirmed', 2,3,12, 7),  -- m15 DeBruyne 2-1 Salah    ✓

    -- Groupe B (id=4) : CR7(3) Kane(13) Bellingham(11)
    ('group','1','confirmed', 2,4, 3,13),  -- m16 CR7  3-1 Kane       ✓
    ('group','1','confirmed', 2,4, 3,11),  -- m17 CR7  2-0 Bellingham ✓
    ('group','1','confirmed', 2,4,13,11),  -- m18 Kane 1-0 Bellingham ✓

    -- Groupe C (id=5) : Mbappe(6) Haaland(9) Vinicius(10)
    ('group','1','confirmed', 2,5, 6, 9),  -- m19 Mbappe  2-1 Haaland  ✓
    ('group','1','confirmed', 2,5, 6,10),  -- m20 Mbappe  2-0 Vinicius ✓
    ('group','1','confirmed', 2,5, 9,10),  -- m21 Haaland 2-0 Vinicius ✓

    -- Groupe D (id=6) : Neymar(5) Benzema(8) Messi(4)
    ('group','1','confirmed', 2,6, 5, 8),  -- m22 Neymar  2-1 Benzema ✓
    ('group','1','confirmed', 2,6, 5, 4),  -- m23 Neymar  0-1 Messi   ✓ (Messi gagne away)
    ('group','1','confirmed', 2,6, 8, 4),  -- m24 Benzema 1-1 Messi   ✓ (nul)

    -- QF (id_groupe=NULL) — leg='1' (aller)
    ('QF','1','confirmed', 2,NULL, 2,13),  -- m25 Zizou    2-0 Kane   ✓ → Zizou avance
    ('QF','1','confirmed', 2,NULL,12, 3),  -- m26 DeBruyne 1-3 CR7    ✓ → CR7 avance
    ('QF','1','pending',   2,NULL, 6, 5),  -- m27 Mbappe vs Neymar  (pas joué)
    ('QF','1','pending',   2,NULL, 9, 4),  -- m28 Haaland vs Messi  (pas joué)

    -- ── CUP 3 — VFoot Masters ──────────────────────────────────
    -- Groupe E (id=7) : Zizou(2) CR7(3) Mbappe(6) Kane(13)
    ('group','1','confirmed', 3,7, 2, 3),  -- m29 Zizou  2-1 CR7   ✓
    ('group','1','confirmed', 3,7, 2, 6),  -- m30 Zizou  1-0 Mbappe ✓
    ('group','1','confirmed', 3,7, 2,13),  -- m31 Zizou  3-0 Kane   ✓
    ('group','1','confirmed', 3,7, 3, 6),  -- m32 CR7    2-2 Mbappe ✓ (nul)
    ('group','1','confirmed', 3,7, 3,13),  -- m33 CR7    2-0 Kane   ✓
    ('group','1','confirmed', 3,7, 6,13),  -- m34 Mbappe 1-0 Kane   ✓

    -- Groupe F (id=8) : Haaland(9) Salah(7) Benzema(8) Neymar(5)
    ('group','1','confirmed', 3,8, 9, 7),  -- m35 Haaland 2-1 Salah   ✓
    ('group','1','confirmed', 3,8, 9, 8),  -- m36 Haaland 1-1 Benzema ✓ (nul)
    ('group','1','confirmed', 3,8, 9, 5),  -- m37 Haaland 3-0 Neymar  ✓
    ('group','1','confirmed', 3,8, 7, 8),  -- m38 Salah   2-1 Benzema ✓
    ('group','1','confirmed', 3,8, 7, 5),  -- m39 Salah   2-0 Neymar  ✓
    ('group','1','confirmed', 3,8, 8, 5),  -- m40 Benzema 2-1 Neymar  ✓

    -- SF (id_groupe=NULL) — leg='1'
    ('SF','1','confirmed', 3,NULL, 2, 7),  -- m41 Zizou   2-0 Salah   ✓ → Zizou en finale
    ('SF','1','confirmed', 3,NULL, 9, 3),  -- m42 Haaland 1-2 CR7     ✓ → CR7 en finale

    -- Finale — leg='single'
    ('F','single','confirmed', 3,NULL, 2, 3)  -- m43 Zizou 3-1 CR7  🏆 ZIZOU CHAMPION
");
echo "Matchs insérés (43).\n";

// ============================================================
// SOUMISSIONS
// Tous les matchs confirmés : les deux joueurs ont soumis le
// même score. Match disputé (m10) : scores différents.
// m4 : seulement CR7 a soumis.
// ============================================================
$pdo->exec("
    INSERT INTO submission (home_score, away_score, id_match, id_user) VALUES

    -- Cup 1 — matchs confirmés
    (2,1, 1,  2), (2,1, 1,  3),   -- m1  Zizou 2-1 CR7
    (3,1, 2,  4), (3,1, 2,  5),   -- m2  Messi 3-1 Neymar
    -- m4 : CR7 seul soumis 2-0
    (2,0, 4,  3),
    -- m7  Mbappe 3-0 Salah
    (3,0, 7,  6), (3,0, 7,  7),
    -- m8  Benzema 2-2 Haaland (nul)
    (2,2, 8,  8), (2,2, 8,  9),
    -- m10 LITIGE — Salah dit 2-1 (pour lui), Haaland dit 0-2 (pour lui)
    (2,1,10,  7), (0,2,10,  9),

    -- Cup 2 — groupes (tous confirmés, ids 13..24)
    (2,0,13,  2), (2,0,13, 12),   -- m13 Zizou    2-0 DeBruyne
    (3,1,14,  2), (3,1,14,  7),   -- m14 Zizou    3-1 Salah
    (2,1,15, 12), (2,1,15,  7),   -- m15 DeBruyne 2-1 Salah
    (3,1,16,  3), (3,1,16, 13),   -- m16 CR7      3-1 Kane
    (2,0,17,  3), (2,0,17, 11),   -- m17 CR7      2-0 Bellingham
    (1,0,18, 13), (1,0,18, 11),   -- m18 Kane     1-0 Bellingham
    (2,1,19,  6), (2,1,19,  9),   -- m19 Mbappe   2-1 Haaland
    (2,0,20,  6), (2,0,20, 10),   -- m20 Mbappe   2-0 Vinicius
    (2,0,21,  9), (2,0,21, 10),   -- m21 Haaland  2-0 Vinicius
    (2,1,22,  5), (2,1,22,  8),   -- m22 Neymar   2-1 Benzema
    (0,1,23,  5), (0,1,23,  4),   -- m23 Neymar   0-1 Messi
    (1,1,24,  8), (1,1,24,  4),   -- m24 Benzema  1-1 Messi (nul)

    -- Cup 2 — QF confirmés
    (2,0,25,  2), (2,0,25, 13),   -- m25 Zizou    2-0 Kane
    (1,3,26, 12), (1,3,26,  3),   -- m26 DeBruyne 1-3 CR7

    -- Cup 3 — Groupe E (m29..m34)
    (2,1,29,  2), (2,1,29,  3),   -- m29 Zizou  2-1 CR7
    (1,0,30,  2), (1,0,30,  6),   -- m30 Zizou  1-0 Mbappe
    (3,0,31,  2), (3,0,31, 13),   -- m31 Zizou  3-0 Kane
    (2,2,32,  3), (2,2,32,  6),   -- m32 CR7    2-2 Mbappe (nul)
    (2,0,33,  3), (2,0,33, 13),   -- m33 CR7    2-0 Kane
    (1,0,34,  6), (1,0,34, 13),   -- m34 Mbappe 1-0 Kane

    -- Cup 3 — Groupe F (m35..m40)
    (2,1,35,  9), (2,1,35,  7),   -- m35 Haaland 2-1 Salah
    (1,1,36,  9), (1,1,36,  8),   -- m36 Haaland 1-1 Benzema (nul)
    (3,0,37,  9), (3,0,37,  5),   -- m37 Haaland 3-0 Neymar
    (2,1,38,  7), (2,1,38,  8),   -- m38 Salah   2-1 Benzema
    (2,0,39,  7), (2,0,39,  5),   -- m39 Salah   2-0 Neymar
    (2,1,40,  8), (2,1,40,  5),   -- m40 Benzema 2-1 Neymar

    -- Cup 3 — SF
    (2,0,41,  2), (2,0,41,  7),   -- m41 Zizou   2-0 Salah
    (1,2,42,  9), (1,2,42,  3),   -- m42 Haaland 1-2 CR7

    -- Cup 3 — Finale
    (3,1,43,  2), (3,1,43,  3)    -- m43 Zizou 3-1 CR7 🏆
");
echo "Soumissions insérées (69).\n";

// ============================================================
// RÉSULTATS  (matchs confirmés uniquement)
// confirmed_by = NULL → accord automatique des deux joueurs
// ============================================================
$pdo->exec("
    INSERT INTO resultat (home_score, away_score, id_match, confirmed_by) VALUES

    -- Cup 1
    (2,1, 1,NULL), (3,1, 2,NULL),                        -- m1, m2
    (3,0, 7,NULL), (2,2, 8,NULL),                        -- m7, m8

    -- Cup 2 — groupes
    (2,0,13,NULL),(3,1,14,NULL),(2,1,15,NULL),           -- m13-m15
    (3,1,16,NULL),(2,0,17,NULL),(1,0,18,NULL),           -- m16-m18
    (2,1,19,NULL),(2,0,20,NULL),(2,0,21,NULL),           -- m19-m21
    (2,1,22,NULL),(0,1,23,NULL),(1,1,24,NULL),           -- m22-m24

    -- Cup 2 — QF
    (2,0,25,NULL),(1,3,26,NULL),                         -- m25-m26

    -- Cup 3 — Groupe E
    (2,1,29,NULL),(1,0,30,NULL),(3,0,31,NULL),           -- m29-m31
    (2,2,32,NULL),(2,0,33,NULL),(1,0,34,NULL),           -- m32-m34

    -- Cup 3 — Groupe F
    (2,1,35,NULL),(1,1,36,NULL),(3,0,37,NULL),           -- m35-m37
    (2,1,38,NULL),(2,0,39,NULL),(2,1,40,NULL),           -- m38-m40

    -- Cup 3 — SF + Finale
    (2,0,41,NULL),(1,2,42,NULL),                         -- m41-m42
    (3,1,43,NULL)                                        -- m43
");
echo "Résultats insérés (33).\n";

// ============================================================
// CLASSEMENTS  (est_classe_dans)
// Colonne played = wins + draws + losses  (contrainte CHECK)
// ============================================================
$pdo->exec("
    INSERT INTO est_classe_dans
        (id_user, id_groupe, played, wins, draws, losses, goals_for, goals_against, points)
    VALUES

    -- ── Cup 1 — Groupe A (id=1) — après 2 matchs sur 6 ──────
    -- Zizou(2) : W vs CR7(2-1)                → 1 joué
    (2, 1, 1, 1, 0, 0, 2, 1, 3),
    -- CR7(3)   : L vs Zizou(1-2)              → 1 joué
    (3, 1, 1, 0, 0, 1, 1, 2, 0),
    -- Messi(4) : W vs Neymar(3-1)             → 1 joué
    (4, 1, 1, 1, 0, 0, 3, 1, 3),
    -- Neymar(5): L vs Messi(1-3)              → 1 joué
    (5, 1, 1, 0, 0, 1, 1, 3, 0),

    -- ── Cup 1 — Groupe B (id=2) — après 2 matchs sur 6 ──────
    -- Mbappe(6): W vs Salah(3-0)              → 1 joué
    (6, 2, 1, 1, 0, 0, 3, 0, 3),
    -- Salah(7) : L vs Mbappe(0-3)             → 1 joué
    (7, 2, 1, 0, 0, 1, 0, 3, 0),
    -- Benzema(8): D vs Haaland(2-2)           → 1 joué
    (8, 2, 1, 0, 1, 0, 2, 2, 1),
    -- Haaland(9): D vs Benzema(2-2)           → 1 joué
    (9, 2, 1, 0, 1, 0, 2, 2, 1),

    -- ── Cup 2 — Groupe A (id=3) — 3 matchs joués (terminé) ──
    -- Zizou(2)    : 2W (vs DeB 2-0, vs Salah 3-1) → GF=5 GA=1
    (2,  3, 2, 2, 0, 0,  5,  1, 6),
    -- DeBruyne(12): 1W 1L (away 0-2, home 2-1)    → GF=2 GA=3
    (12, 3, 2, 1, 0, 1,  2,  3, 3),
    -- Salah(7)    : 2L (away 1-3, away 1-2)        → GF=2 GA=5
    (7,  3, 2, 0, 0, 2,  2,  5, 0),

    -- ── Cup 2 — Groupe B (id=4) ──────────────────────────────
    -- CR7(3)       : 2W (3-1 Kane, 2-0 Bell)       → GF=5 GA=1
    (3,  4, 2, 2, 0, 0,  5,  1, 6),
    -- Kane(13)     : 1W 1L (away 1-3, home 1-0)    → GF=2 GA=3
    (13, 4, 2, 1, 0, 1,  2,  3, 3),
    -- Bellingham(11): 2L (away 0-2, away 0-1)      → GF=0 GA=3
    (11, 4, 2, 0, 0, 2,  0,  3, 0),

    -- ── Cup 2 — Groupe C (id=5) ──────────────────────────────
    -- Mbappe(6)   : 2W (2-1 Haa, 2-0 Vin)         → GF=4 GA=1
    (6,  5, 2, 2, 0, 0,  4,  1, 6),
    -- Haaland(9)  : 1W 1L (away 1-2, home 2-0)    → GF=3 GA=2
    (9,  5, 2, 1, 0, 1,  3,  2, 3),
    -- Vinicius(10): 2L (away 0-2, away 0-2)        → GF=0 GA=4
    (10, 5, 2, 0, 0, 2,  0,  4, 0),

    -- ── Cup 2 — Groupe D (id=6) ──────────────────────────────
    -- Messi(4)    : 1W 1D (away 1-0, away 1-1)     → GF=2 GA=1
    (4,  6, 2, 1, 1, 0,  2,  1, 4),
    -- Neymar(5)   : 1W 1L (home 2-1, home 0-1)     → GF=2 GA=2
    (5,  6, 2, 1, 0, 1,  2,  2, 3),
    -- Benzema(8)  : 1D 1L (away 1-2, home 1-1)     → GF=2 GA=3
    (8,  6, 2, 0, 1, 1,  2,  3, 1),

    -- ── Cup 3 — Groupe E (id=7) — 6 matchs (round-robin complet) ──
    -- Zizou(2)  : 3W (2-1, 1-0, 3-0)               → GF=6 GA=1
    (2,  7, 3, 3, 0, 0,  6,  1, 9),
    -- CR7(3)    : 1W 1D 1L (away 1-2, home 2-2, home 2-0) → GF=5 GA=4
    (3,  7, 3, 1, 1, 1,  5,  4, 4),
    -- Mbappe(6) : 1W 1D 1L (away 0-1, away 2-2, home 1-0) → GF=3 GA=3
    (6,  7, 3, 1, 1, 1,  3,  3, 4),
    -- Kane(13)  : 3L (away 0-3, away 0-2, away 0-1) → GF=0 GA=6
    (13, 7, 3, 0, 0, 3,  0,  6, 0),

    -- ── Cup 3 — Groupe F (id=8) ──────────────────────────────
    -- Haaland(9) : 2W 1D (2-1, 1-1, 3-0)           → GF=6 GA=2
    (9,  8, 3, 2, 1, 0,  6,  2, 7),
    -- Salah(7)   : 2W 1L (away 1-2, home 2-1, home 2-0) → GF=5 GA=3
    (7,  8, 3, 2, 0, 1,  5,  3, 6),
    -- Benzema(8) : 1W 1D 1L (away 1-1, away 1-2, home 2-1) → GF=4 GA=4
    (8,  8, 3, 1, 1, 1,  4,  4, 4),
    -- Neymar(5)  : 3L (away 0-3, away 0-2, away 1-2) → GF=1 GA=7
    (5,  8, 3, 0, 0, 3,  1,  7, 0)
");
echo "Classements insérés.\n";

// ============================================================
// TROPHÉE — VFoot Masters remporté par Zizou
// trophee(id_trophee, nom, icone)
// remporte(id_user, id_trophee, date_obtention)
// ============================================================
$pdo->exec("
    INSERT INTO trophee (nom, icone) VALUES
    ('Champion Liga VFoot S1', NULL),
    ('Champion Arena Cup',     NULL),
    ('Champion VFoot Masters', NULL)
");
// id_trophee : 1=Liga(pas encore attribué), 2=Arena(pas encore), 3=VFoot Masters

$pdo->exec("
    INSERT INTO remporte (id_user, id_trophee) VALUES
    (2, 3)  -- Zizou remporte le trophée VFoot Masters
");
echo "Trophée inséré + attribué à Zizou.\n";

// ============================================================
echo "\n=== Seed terminé ! ===\n\n";
echo "Identifiants (mot de passe universel : 00000000)\n";
echo "  admin@vfoot.com      → VFootAdmin  [admin]\n";
echo "  zizou@vfoot.com      → Zizou       [player — Cup1 grpA, Cup2 grpA, Cup3 grpE]\n";
echo "  cr7@vfoot.com        → CR7         [player — Cup1 grpA, Cup2 grpB, Cup3 grpE]\n";
echo "  messi@vfoot.com      → Messi       [player — Cup1 grpA, Cup2 grpD]\n";
echo "  neymar@vfoot.com     → Neymar      [player — Cup1 grpA, Cup2 grpD, Cup3 grpF]\n";
echo "  mbappe@vfoot.com     → Mbappe      [player — Cup1 grpB, Cup2 grpC, Cup3 grpE]\n";
echo "  salah@vfoot.com      → Salah       [player — Cup1 grpB, Cup2 grpA, Cup3 grpF]\n";
echo "  benzema@vfoot.com    → Benzema     [player — Cup1 grpB, Cup2 grpD, Cup3 grpF]\n";
echo "  haaland@vfoot.com    → Haaland     [player — Cup1 grpB, Cup2 grpC, Cup3 grpF]\n";
echo "  vinicius@vfoot.com   → Vinicius    [player — Cup2 grpC uniquement]\n";
echo "  bellingham@vfoot.com → Bellingham  [player — Cup2 grpB uniquement]\n";
echo "  debruyne@vfoot.com   → DeBruyne    [player — Cup2 grpA uniquement]\n";
echo "  kane@vfoot.com       → Kane        [player — Cup1 grpB? non — Cup2 grpB, Cup3 grpE]\n";
echo "\n";
echo "Cups :\n";
echo "  1 — Liga VFoot S1   → started  (groupes en cours, quelques matchs joués)\n";
echo "  2 — Arena Cup       → started  (QF en cours : Zizou et CR7 avancent, 2 matchs en attente)\n";
echo "  3 — VFoot Masters   → finished (Zizou champion 3-1 vs CR7 en finale)\n";
