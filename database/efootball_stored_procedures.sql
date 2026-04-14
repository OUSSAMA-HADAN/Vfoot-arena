-- ============================================================
-- BASE DE DONNÉES : efootball_tournament
-- Fichier        : efootball_stored_procedures.sql
-- Description    : fonctions et procédures stockées couvrant
--                  toutes les opérations de l'application
-- Dépendance     : efootball_tournament_v2.sql (doit être importé avant)
-- ============================================================

USE efootball_tournament;

-- MySQL exige un délimiteur alternatif pour définir des blocs
-- BEGIN...END sans que le ; interne ne coupe la définition
DELIMITER $$


-- ============================================================
-- SECTION 1 — FONCTIONS SCALAIRES
-- Retournent une valeur simple, utilisables dans un SELECT
-- ============================================================


-- ------------------------------------------------------------
-- fn_user_exists_email
-- Vérifie si un email est déjà utilisé
-- Retourne : 1 = existe | 0 = libre
-- Utilisation : SELECT fn_user_exists_email('test@vfoot.com')
-- ------------------------------------------------------------
CREATE FUNCTION fn_user_exists_email(p_email VARCHAR(100))
RETURNS TINYINT(1)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*)
    INTO   v_count
    FROM   users
    WHERE  email = p_email;

    RETURN (v_count > 0);
END$$


-- ------------------------------------------------------------
-- fn_user_exists_username
-- Vérifie si un username est déjà pris
-- Retourne : 1 = pris | 0 = disponible
-- Utilisation : SELECT fn_user_exists_username('Mehdi')
-- ------------------------------------------------------------
CREATE FUNCTION fn_user_exists_username(p_username VARCHAR(50))
RETURNS TINYINT(1)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*)
    INTO   v_count
    FROM   users
    WHERE  username = p_username;

    RETURN (v_count > 0);
END$$


-- ------------------------------------------------------------
-- fn_user_in_cup
-- Vérifie si un joueur est déjà inscrit à une cup
-- Retourne : 1 = inscrit | 0 = non inscrit
-- Utilisation : SELECT fn_user_in_cup(3, 1)
-- ------------------------------------------------------------
CREATE FUNCTION fn_user_in_cup(p_id_user INT, p_id_cup INT)
RETURNS TINYINT(1)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*)
    INTO   v_count
    FROM   participe
    WHERE  id_user = p_id_user
      AND  id_cup  = p_id_cup;

    RETURN (v_count > 0);
END$$


-- ------------------------------------------------------------
-- fn_cup_is_full
-- Vérifie si une cup a atteint son nombre maximum de joueurs
-- Retourne : 1 = pleine | 0 = places disponibles
-- Utilisation : SELECT fn_cup_is_full(1)
-- ------------------------------------------------------------
CREATE FUNCTION fn_cup_is_full(p_id_cup INT)
RETURNS TINYINT(1)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_max_players INT;
    DECLARE v_current     INT;

    SELECT max_players INTO v_max_players FROM cup WHERE id_cup = p_id_cup;

    SELECT COUNT(*)
    INTO   v_current
    FROM   participe
    WHERE  id_cup = p_id_cup
      AND  role   = 'player';

    RETURN (v_current >= v_max_players);
END$$


-- ------------------------------------------------------------
-- fn_aggregate_home
-- Calcule le score agrégé du joueur domicile (leg 1)
-- sur un duel knockout (leg 1 + leg 2)
-- Retourne : total buts de home_user leg1 sur les deux manches
-- Utilisation : SELECT fn_aggregate_home(5)  -- id du leg 1
-- ------------------------------------------------------------
CREATE FUNCTION fn_aggregate_home(p_first_leg_id INT)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_leg1_home INT DEFAULT 0;  -- buts marqués par home en leg 1
    DECLARE v_leg2_away INT DEFAULT 0;  -- buts marqués par home en leg 2 (il jouait away)

    -- leg 1 : home_score = buts de home_user
    SELECT COALESCE(r.home_score, 0)
    INTO   v_leg1_home
    FROM   resultat r
    WHERE  r.id_match = p_first_leg_id;

    -- leg 2 : away_score = buts de home_user (rôles inversés)
    SELECT COALESCE(r.away_score, 0)
    INTO   v_leg2_away
    FROM   resultat  r
    JOIN   matche    m ON r.id_match = m.id_match
    WHERE  m.first_leg_id = p_first_leg_id
      AND  m.leg          = '2';

    RETURN v_leg1_home + v_leg2_away;
END$$


-- ------------------------------------------------------------
-- fn_aggregate_away
-- Calcule le score agrégé du joueur extérieur (leg 1)
-- Retourne : total buts de away_user leg1 sur les deux manches
-- Utilisation : SELECT fn_aggregate_away(5)
-- ------------------------------------------------------------
CREATE FUNCTION fn_aggregate_away(p_first_leg_id INT)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_leg1_away INT DEFAULT 0;  -- buts marqués par away en leg 1
    DECLARE v_leg2_home INT DEFAULT 0;  -- buts marqués par away en leg 2 (il jouait home)

    -- leg 1 : away_score = buts de away_user
    SELECT COALESCE(r.away_score, 0)
    INTO   v_leg1_away
    FROM   resultat r
    WHERE  r.id_match = p_first_leg_id;

    -- leg 2 : home_score = buts de away_user (rôles inversés)
    SELECT COALESCE(r.home_score, 0)
    INTO   v_leg2_home
    FROM   resultat  r
    JOIN   matche    m ON r.id_match = m.id_match
    WHERE  m.first_leg_id = p_first_leg_id
      AND  m.leg          = '2';

    RETURN v_leg1_away + v_leg2_home;
END$$


-- ============================================================
-- SECTION 2 — PROCÉDURES : USERS
-- ============================================================


-- ------------------------------------------------------------
-- sp_create_user
-- Inscrit un nouvel utilisateur (le hash password vient du PHP)
-- id_role = 1 (player) par défaut — seul un admin peut promouvoir
-- Retourne : id_user généré
-- ------------------------------------------------------------
CREATE PROCEDURE sp_create_user(
    IN  p_email    VARCHAR(100),
    IN  p_username VARCHAR(50),
    IN  p_password VARCHAR(255),          -- hash bcrypt fourni par l'application
    OUT p_id_user  INT
)
BEGIN
    -- id_role 1 = player (défaut) — récupéré depuis la table role pour éviter le hardcode
    DECLARE v_id_role INT;

    SELECT id_role INTO v_id_role FROM role WHERE nom = 'player' LIMIT 1;

    INSERT INTO users (email, username, password, id_role)
    VALUES (p_email, p_username, p_password, v_id_role);

    SET p_id_user = LAST_INSERT_ID();
END$$


-- ------------------------------------------------------------
-- sp_get_user_by_email
-- Récupère un utilisateur par son email (connexion / auth)
-- Retourne : données utilisateur + nom du rôle (JOIN role)
-- Le JOIN role évite de stocker la chaîne 'admin'/'player'
-- directement dans la session — on lit le nom depuis la DB
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_user_by_email(IN p_email VARCHAR(100))
BEGIN
    SELECT
        u.id_user,
        u.email,
        u.password,
        u.username,
        u.id_role,
        r.nom      AS role,               -- nom lisible du rôle pour la session
        u.is_banned,
        u.created_at
    FROM   users u
    JOIN   role  r ON u.id_role = r.id_role
    WHERE  u.email = p_email
    LIMIT  1;
END$$


-- ------------------------------------------------------------
-- sp_get_user_by_id
-- Récupère un utilisateur par son id (profil, vérification session)
-- Retourne : données utilisateur + nom du rôle (JOIN role)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_user_by_id(IN p_id_user INT)
BEGIN
    SELECT
        u.id_user,
        u.email,
        u.username,
        u.id_role,
        r.nom      AS role,               -- nom lisible du rôle
        u.is_banned,
        u.created_at
    FROM   users u
    JOIN   role  r ON u.id_role = r.id_role
    WHERE  u.id_user = p_id_user
    LIMIT  1;
END$$


-- ------------------------------------------------------------
-- sp_ban_user
-- Bascule le statut banni d'un utilisateur (admin uniquement)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_ban_user(IN p_id_user INT, IN p_banned TINYINT(1))
BEGIN
    UPDATE users
    SET    is_banned = p_banned
    WHERE  id_user   = p_id_user;
END$$


-- ------------------------------------------------------------
-- sp_set_user_role
-- Change le rôle d'un utilisateur (admin uniquement)
-- p_role_nom : 'player' | 'admin' | tout futur rôle ajouté
-- ------------------------------------------------------------
CREATE PROCEDURE sp_set_user_role(IN p_id_user INT, IN p_role_nom VARCHAR(50))
BEGIN
    DECLARE v_id_role INT;

    SELECT id_role INTO v_id_role FROM role WHERE nom = p_role_nom LIMIT 1;

    IF v_id_role IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Rôle inconnu — vérifier la table role';
    END IF;

    UPDATE users
    SET    id_role = v_id_role
    WHERE  id_user = p_id_user;
END$$


-- ============================================================
-- SECTION 3 — PROCÉDURES : CUPS
-- ============================================================


-- ------------------------------------------------------------
-- sp_get_open_cups
-- Liste les cups publiques ouvertes (page d'accueil)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_open_cups()
BEGIN
    SELECT
        c.id_cup,
        c.titre,
        c.statut,
        c.min_players,
        c.max_players,
        c.created_at,
        u.username   AS organisateur,
        COUNT(p.id_user) AS nb_inscrits   -- nombre de joueurs actuellement inscrits
    FROM   cup      c
    JOIN   users    u ON c.created_by = u.id_user
    LEFT JOIN participe p ON p.id_cup = c.id_cup AND p.role = 'player'
    WHERE  c.visibilite = 'public'
      AND  c.statut     IN ('open', 'started')
    GROUP BY c.id_cup
    ORDER BY c.created_at DESC;
END$$


-- ------------------------------------------------------------
-- sp_get_cup_detail
-- Détail complet d'une cup + ses groupes + joueurs inscrits
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_cup_detail(IN p_id_cup INT)
BEGIN
    -- informations de la cup
    SELECT
        c.id_cup, c.titre, c.visibilite, c.statut,
        c.min_players, c.max_players, c.qualification, c.created_at,
        u.username AS organisateur
    FROM   cup   c
    JOIN   users u ON c.created_by = u.id_user
    WHERE  c.id_cup = p_id_cup;

    -- joueurs inscrits à cette cup
    SELECT
        u.id_user, u.username, p.role, p.date_adhesion
    FROM   participe p
    JOIN   users     u ON p.id_user = u.id_user
    WHERE  p.id_cup = p_id_cup
    ORDER BY p.date_adhesion;

    -- groupes de cette cup
    SELECT id_groupe, nom
    FROM   groupe
    WHERE  id_cup = p_id_cup
    ORDER BY nom;
END$$


-- ------------------------------------------------------------
-- sp_create_cup
-- Crée une nouvelle cup et inscrit l'organisateur
-- Retourne : id_cup généré
-- ------------------------------------------------------------
CREATE PROCEDURE sp_create_cup(
    IN  p_titre       VARCHAR(100),
    IN  p_visibilite  ENUM('public','private'),
    IN  p_join_code   VARCHAR(20),            -- NULL si public
    IN  p_min_players INT,
    IN  p_max_players INT,
    IN  p_created_by  INT,
    OUT p_id_cup      INT
)
BEGIN
    INSERT INTO cup (titre, visibilite, join_code, min_players, max_players, statut, created_by)
    VALUES (p_titre, p_visibilite, p_join_code, p_min_players, p_max_players, 'open', p_created_by);

    SET p_id_cup = LAST_INSERT_ID();

    -- inscrire automatiquement le créateur comme organisateur
    INSERT INTO participe (id_user, id_cup, role)
    VALUES (p_created_by, p_id_cup, 'organizer');
END$$


-- ------------------------------------------------------------
-- sp_join_cup
-- Inscrit un joueur à une cup (public ou privé avec code)
-- Retourne : 0 = ok | 1 = cup pleine | 2 = code invalide | 3 = déjà inscrit
-- ------------------------------------------------------------
CREATE PROCEDURE sp_join_cup(
    IN  p_id_user  INT,
    IN  p_id_cup   INT,
    IN  p_join_code VARCHAR(20),            -- NULL si cup publique
    OUT p_result   INT                      -- code de retour
)
BEGIN
    DECLARE v_visibilite  ENUM('public','private');
    DECLARE v_code_stored VARCHAR(20);
    DECLARE v_statut      ENUM('draft','open','started','finished','cancelled');

    SELECT visibilite, join_code, statut
    INTO   v_visibilite, v_code_stored, v_statut
    FROM   cup
    WHERE  id_cup = p_id_cup;

    -- déjà inscrit
    IF fn_user_in_cup(p_id_user, p_id_cup) THEN
        SET p_result = 3;

    -- cup pleine
    ELSEIF fn_cup_is_full(p_id_cup) THEN
        SET p_result = 1;

    -- cup privée : vérifier le code
    ELSEIF v_visibilite = 'private' AND v_code_stored <> p_join_code THEN
        SET p_result = 2;

    ELSE
        INSERT INTO participe (id_user, id_cup, role)
        VALUES (p_id_user, p_id_cup, 'player');
        SET p_result = 0;
    END IF;
END$$


-- ------------------------------------------------------------
-- sp_update_cup_statut
-- Change le statut d'une cup (organisateur ou admin)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_update_cup_statut(IN p_id_cup INT, IN p_statut ENUM('draft','open','started','finished','cancelled'))
BEGIN
    UPDATE cup
    SET    statut = p_statut
    WHERE  id_cup = p_id_cup;
END$$


-- ------------------------------------------------------------
-- sp_get_cups_by_user
-- Cups auxquelles un joueur participe
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_cups_by_user(IN p_id_user INT)
BEGIN
    SELECT
        c.id_cup, c.titre, c.statut, c.visibilite,
        p.role, p.date_adhesion
    FROM   participe p
    JOIN   cup       c ON p.id_cup = c.id_cup
    WHERE  p.id_user = p_id_user
    ORDER BY p.date_adhesion DESC;
END$$


-- ============================================================
-- SECTION 4 — PROCÉDURES : GROUPES
-- ============================================================


-- ------------------------------------------------------------
-- sp_create_group
-- Crée un groupe dans une cup et y ajoute les joueurs
-- ------------------------------------------------------------
CREATE PROCEDURE sp_create_group(
    IN  p_nom     CHAR(1),
    IN  p_id_cup  INT,
    OUT p_id_groupe INT
)
BEGIN
    INSERT INTO groupe (nom, id_cup)
    VALUES (p_nom, p_id_cup);

    SET p_id_groupe = LAST_INSERT_ID();
END$$


-- ------------------------------------------------------------
-- sp_add_player_to_group
-- Ajoute un joueur dans un groupe (initialise ses stats à 0)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_add_player_to_group(IN p_id_user INT, IN p_id_groupe INT)
BEGIN
    INSERT IGNORE INTO est_classe_dans (id_user, id_groupe)
    VALUES (p_id_user, p_id_groupe);
    -- INSERT IGNORE évite l'erreur si le joueur est déjà dans le groupe
END$$


-- ------------------------------------------------------------
-- sp_get_group_standings
-- Classement d'un groupe trié par points puis différence de buts
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_group_standings(IN p_id_groupe INT)
BEGIN
    SELECT
        u.id_user,
        u.username,
        e.played,
        e.wins,
        e.draws,
        e.losses,
        e.goals_for,
        e.goals_against,
        (e.goals_for - e.goals_against) AS goal_diff,  -- différence de buts
        e.points
    FROM   est_classe_dans e
    JOIN   users           u ON e.id_user   = u.id_user
    WHERE  e.id_groupe = p_id_groupe
    ORDER BY
        e.points        DESC,
        goal_diff       DESC,       -- critère secondaire : différence de buts
        e.goals_for     DESC;       -- critère tertiaire  : buts marqués
END$$


-- ------------------------------------------------------------
-- sp_update_group_standings
-- Met à jour le classement d'un groupe après un match confirmé
-- Appelée par l'application après chaque confirmation de score
-- ------------------------------------------------------------
CREATE PROCEDURE sp_update_group_standings(
    IN p_id_match     INT,
    IN p_home_score   INT,
    IN p_away_score   INT
)
BEGIN
    DECLARE v_home_user_id INT;
    DECLARE v_away_user_id INT;
    DECLARE v_id_groupe    INT;

    -- récupérer les joueurs et le groupe du match
    SELECT home_user_id, away_user_id, id_groupe
    INTO   v_home_user_id, v_away_user_id, v_id_groupe
    FROM   matche
    WHERE  id_match = p_id_match;

    IF p_home_score > p_away_score THEN
        -- victoire domicile
        UPDATE est_classe_dans
        SET played       = played + 1,
            wins         = wins   + 1,
            points       = points + 3,
            goals_for    = goals_for    + p_home_score,
            goals_against= goals_against + p_away_score
        WHERE id_user = v_home_user_id AND id_groupe = v_id_groupe;

        UPDATE est_classe_dans
        SET played       = played + 1,
            losses       = losses + 1,
            goals_for    = goals_for    + p_away_score,
            goals_against= goals_against + p_home_score
        WHERE id_user = v_away_user_id AND id_groupe = v_id_groupe;

    ELSEIF p_away_score > p_home_score THEN
        -- victoire extérieur
        UPDATE est_classe_dans
        SET played       = played + 1,
            losses       = losses + 1,
            goals_for    = goals_for    + p_home_score,
            goals_against= goals_against + p_away_score
        WHERE id_user = v_home_user_id AND id_groupe = v_id_groupe;

        UPDATE est_classe_dans
        SET played       = played + 1,
            wins         = wins   + 1,
            points       = points + 3,
            goals_for    = goals_for    + p_away_score,
            goals_against= goals_against + p_home_score
        WHERE id_user = v_away_user_id AND id_groupe = v_id_groupe;

    ELSE
        -- match nul
        UPDATE est_classe_dans
        SET played       = played + 1,
            draws        = draws  + 1,
            points       = points + 1,
            goals_for    = goals_for    + p_home_score,
            goals_against= goals_against + p_away_score
        WHERE id_user = v_home_user_id AND id_groupe = v_id_groupe;

        UPDATE est_classe_dans
        SET played       = played + 1,
            draws        = draws  + 1,
            points       = points + 1,
            goals_for    = goals_for    + p_away_score,
            goals_against= goals_against + p_home_score
        WHERE id_user = v_away_user_id AND id_groupe = v_id_groupe;
    END IF;
END$$


-- ============================================================
-- SECTION 5 — PROCÉDURES : MATCHS
-- ============================================================


-- ------------------------------------------------------------
-- sp_create_match
-- Crée un match (groupe ou knockout)
-- Retourne : id_match généré
-- ------------------------------------------------------------
CREATE PROCEDURE sp_create_match(
    IN  p_round        ENUM('group','R16','QF','SF','F'),
    IN  p_leg          ENUM('1','2','3','single'),
    IN  p_id_cup       INT,
    IN  p_id_groupe    INT,                -- NULL si knockout
    IN  p_home_user_id INT,
    IN  p_away_user_id INT,
    IN  p_first_leg_id INT,                -- NULL si leg 1 ou groupe
    IN  p_deadline     DATETIME,           -- NULL si pas de deadline
    OUT p_id_match     INT
)
BEGIN
    INSERT INTO matche (round, leg, id_cup, id_groupe, home_user_id, away_user_id, first_leg_id, deadline)
    VALUES (p_round, p_leg, p_id_cup, p_id_groupe, p_home_user_id, p_away_user_id, p_first_leg_id, p_deadline);

    SET p_id_match = LAST_INSERT_ID();
END$$


-- ------------------------------------------------------------
-- sp_get_match_detail
-- Détail complet d'un match avec soumissions et résultat
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_match_detail(IN p_id_match INT)
BEGIN
    SELECT
        m.id_match, m.round, m.leg, m.statut, m.deadline, m.created_at,
        h.id_user  AS home_user_id,   h.username AS home_name,
        a.id_user  AS away_user_id,   a.username AS away_name,
        c.titre    AS cup,
        g.nom      AS groupe,
        r.home_score  AS result_home, r.away_score  AS result_away,
        sh.home_score AS sub_home_h,  sh.away_score AS sub_away_h,  -- soumission joueur domicile
        sa.home_score AS sub_home_a,  sa.away_score AS sub_away_a   -- soumission joueur extérieur
    FROM      matche     m
    JOIN      users      h  ON m.home_user_id = h.id_user
    JOIN      users      a  ON m.away_user_id = a.id_user
    JOIN      cup        c  ON m.id_cup       = c.id_cup
    LEFT JOIN groupe     g  ON m.id_groupe    = g.id_groupe
    LEFT JOIN resultat   r  ON r.id_match     = m.id_match
    LEFT JOIN submission sh ON sh.id_match    = m.id_match AND sh.id_user = m.home_user_id
    LEFT JOIN submission sa ON sa.id_match    = m.id_match AND sa.id_user = m.away_user_id
    WHERE m.id_match = p_id_match;
END$$


-- ------------------------------------------------------------
-- sp_get_matches_by_user
-- Matchs d'un joueur (en attente en premier)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_matches_by_user(IN p_id_user INT)
BEGIN
    SELECT
        m.id_match, m.round, m.leg, m.statut, m.deadline,
        h.username AS home_name,
        a.username AS away_name,
        c.titre    AS cup,
        r.home_score, r.away_score
    FROM      matche   m
    JOIN      users    h ON m.home_user_id = h.id_user
    JOIN      users    a ON m.away_user_id = a.id_user
    JOIN      cup      c ON m.id_cup       = c.id_cup
    LEFT JOIN resultat r ON r.id_match     = m.id_match
    WHERE (m.home_user_id = p_id_user OR m.away_user_id = p_id_user)
    ORDER BY
        FIELD(m.statut, 'pending', 'submitted', 'disputed', 'confirmed', 'forfeited'),
        m.deadline ASC;
END$$


-- ------------------------------------------------------------
-- sp_get_group_matches
-- Tous les matchs d'un groupe avec soumissions et résultat
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_group_matches(IN p_id_groupe INT)
BEGIN
    SELECT
        m.id_match, m.leg, m.statut,
        h.username AS home_name,
        a.username AS away_name,
        m.home_user_id, m.away_user_id,
        r.home_score  AS result_home,  r.away_score  AS result_away,
        sh.home_score AS sub_home_h,   sh.away_score AS sub_away_h,
        sa.home_score AS sub_home_a,   sa.away_score AS sub_away_a
    FROM      matche     m
    JOIN      users      h  ON m.home_user_id = h.id_user
    JOIN      users      a  ON m.away_user_id = a.id_user
    LEFT JOIN resultat   r  ON r.id_match     = m.id_match
    LEFT JOIN submission sh ON sh.id_match    = m.id_match AND sh.id_user = m.home_user_id
    LEFT JOIN submission sa ON sa.id_match    = m.id_match AND sa.id_user = m.away_user_id
    WHERE  m.id_groupe = p_id_groupe
    ORDER BY m.leg, m.id_match;
END$$


-- ------------------------------------------------------------
-- sp_get_disputed_matches
-- Matchs en litige — pour le tableau de bord de l'organisateur
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_disputed_matches()
BEGIN
    SELECT
        m.id_match, m.round, m.leg,
        h.username AS home_name,
        a.username AS away_name,
        c.titre    AS cup,
        sh.home_score AS sub_home_h, sh.away_score AS sub_away_h,
        sa.home_score AS sub_home_a, sa.away_score AS sub_away_a,
        m.created_at
    FROM      matche     m
    JOIN      users      h  ON m.home_user_id = h.id_user
    JOIN      users      a  ON m.away_user_id = a.id_user
    JOIN      cup        c  ON m.id_cup       = c.id_cup
    LEFT JOIN submission sh ON sh.id_match    = m.id_match AND sh.id_user = m.home_user_id
    LEFT JOIN submission sa ON sa.id_match    = m.id_match AND sa.id_user = m.away_user_id
    WHERE  m.statut = 'disputed'
    ORDER BY m.created_at ASC;                                    -- les plus anciens en premier
END$$


-- ------------------------------------------------------------
-- sp_update_match_statut
-- Met à jour le statut d'un match
-- ------------------------------------------------------------
CREATE PROCEDURE sp_update_match_statut(
    IN p_id_match INT,
    IN p_statut   ENUM('pending','submitted','disputed','confirmed','forfeited')
)
BEGIN
    UPDATE matche
    SET    statut = p_statut
    WHERE  id_match = p_id_match;
END$$


-- ============================================================
-- SECTION 6 — PROCÉDURES : SOUMISSIONS & RÉSULTATS
-- ============================================================


-- ------------------------------------------------------------
-- sp_submit_score
-- Soumet ou met à jour le score d'un joueur pour un match
-- Vérifie ensuite si les deux joueurs ont soumis :
--   → accord  : confirme automatiquement (confirmed_by = NULL)
--   → désaccord : passe en disputed
-- Retourne : 'confirmed' | 'disputed' | 'waiting'
-- ------------------------------------------------------------
CREATE PROCEDURE sp_submit_score(
    IN  p_id_match   INT,
    IN  p_id_user    INT,
    IN  p_home_score INT,
    IN  p_away_score INT,
    OUT p_status     VARCHAR(20)
)
BEGIN
    DECLARE v_count       INT;
    DECLARE v_home1       INT;
    DECLARE v_away1       INT;
    DECLARE v_home2       INT;
    DECLARE v_away2       INT;
    DECLARE v_home_user   INT;
    DECLARE v_away_user   INT;
    DECLARE v_round       ENUM('group','R16','QF','SF','F');
    DECLARE v_id_groupe   INT;

    -- insérer ou mettre à jour la soumission du joueur
    INSERT INTO submission (home_score, away_score, id_match, id_user)
    VALUES (p_home_score, p_away_score, p_id_match, p_id_user)
    ON DUPLICATE KEY UPDATE
        home_score = VALUES(home_score),
        away_score = VALUES(away_score);

    -- compter le nombre de soumissions pour ce match
    SELECT COUNT(*) INTO v_count
    FROM   submission
    WHERE  id_match = p_id_match;

    IF v_count < 2 THEN
        -- un seul joueur a soumis — on attend l'autre
        UPDATE matche SET statut = 'submitted' WHERE id_match = p_id_match;
        SET p_status = 'waiting';

    ELSE
        -- les deux joueurs ont soumis — récupérer les deux soumissions
        SELECT home_user_id, away_user_id, round, id_groupe
        INTO   v_home_user, v_away_user, v_round, v_id_groupe
        FROM   matche
        WHERE  id_match = p_id_match;

        SELECT home_score, away_score INTO v_home1, v_away1
        FROM   submission WHERE id_match = p_id_match AND id_user = v_home_user;

        SELECT home_score, away_score INTO v_home2, v_away2
        FROM   submission WHERE id_match = p_id_match AND id_user = v_away_user;

        IF v_home1 = v_home2 AND v_away1 = v_away2 THEN
            -- accord → confirmation automatique
            INSERT INTO resultat (home_score, away_score, id_match, confirmed_by)
            VALUES (v_home1, v_away1, p_id_match, NULL);

            UPDATE matche SET statut = 'confirmed' WHERE id_match = p_id_match;

            -- mettre à jour le classement si c'est un match de groupe
            IF v_round = 'group' AND v_id_groupe IS NOT NULL THEN
                CALL sp_update_group_standings(p_id_match, v_home1, v_away1);
            END IF;

            SET p_status = 'confirmed';
        ELSE
            -- désaccord → litige
            UPDATE matche SET statut = 'disputed' WHERE id_match = p_id_match;
            SET p_status = 'disputed';
        END IF;
    END IF;
END$$


-- ------------------------------------------------------------
-- sp_resolve_dispute
-- L'organisateur impose le score final d'un match en litige
-- confirmed_by = id de l'organisateur qui a tranché
-- ------------------------------------------------------------
CREATE PROCEDURE sp_resolve_dispute(
    IN p_id_match    INT,
    IN p_home_score  INT,
    IN p_away_score  INT,
    IN p_resolved_by INT            -- id_user de l'organisateur
)
BEGIN
    DECLARE v_round     ENUM('group','R16','QF','SF','F');
    DECLARE v_id_groupe INT;

    -- supprimer un éventuel résultat précédent (ne devrait pas en avoir, mais sécurité)
    DELETE FROM resultat WHERE id_match = p_id_match;

    -- insérer le score final imposé par l'organisateur
    INSERT INTO resultat (home_score, away_score, id_match, confirmed_by)
    VALUES (p_home_score, p_away_score, p_id_match, p_resolved_by);

    UPDATE matche SET statut = 'confirmed' WHERE id_match = p_id_match;

    -- mettre à jour le classement si c'est un match de groupe
    SELECT round, id_groupe INTO v_round, v_id_groupe
    FROM   matche WHERE id_match = p_id_match;

    IF v_round = 'group' AND v_id_groupe IS NOT NULL THEN
        CALL sp_update_group_standings(p_id_match, p_home_score, p_away_score);
    END IF;
END$$


-- ------------------------------------------------------------
-- sp_check_aggregate
-- Calcule l'agrégat après les deux manches d'un duel knockout
-- Crée le leg 3 (barrage) si égalité
-- Retourne : winner_id (0 si barrage créé)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_check_aggregate(
    IN  p_leg2_id   INT,
    OUT p_winner_id INT          -- id_user du qualifié, 0 = barrage créé
)
BEGIN
    DECLARE v_first_leg_id INT;
    DECLARE v_score_home   INT;
    DECLARE v_score_away   INT;
    DECLARE v_home_user    INT;
    DECLARE v_away_user    INT;
    DECLARE v_round        ENUM('group','R16','QF','SF','F');
    DECLARE v_id_cup       INT;

    SELECT first_leg_id, round, id_cup
    INTO   v_first_leg_id, v_round, v_id_cup
    FROM   matche
    WHERE  id_match = p_leg2_id;

    -- score agrégé via les fonctions scalaires
    SET v_score_home = fn_aggregate_home(v_first_leg_id);
    SET v_score_away = fn_aggregate_away(v_first_leg_id);

    SELECT home_user_id, away_user_id
    INTO   v_home_user, v_away_user
    FROM   matche
    WHERE  id_match = v_first_leg_id;

    IF v_score_home > v_score_away THEN
        SET p_winner_id = v_home_user;

    ELSEIF v_score_away > v_score_home THEN
        SET p_winner_id = v_away_user;

    ELSE
        -- égalité → créer le barrage (leg 3)
        INSERT INTO matche (round, leg, id_cup, home_user_id, away_user_id, first_leg_id)
        VALUES (v_round, '3', v_id_cup, v_home_user, v_away_user, v_first_leg_id);

        SET p_winner_id = 0;                                      -- 0 = barrage créé
    END IF;
END$$


-- ============================================================
-- SECTION 7 — PROCÉDURES : STATS JOUEUR
-- ============================================================


-- ------------------------------------------------------------
-- sp_update_player_stats
-- Met à jour les stats globales d'un joueur après un match confirmé
-- Appelée après sp_submit_score ou sp_resolve_dispute
-- ------------------------------------------------------------
CREATE PROCEDURE sp_update_player_stats(
    IN p_id_user    INT,
    IN p_phase      ENUM('group','knockout'),
    IN p_win        INT,                          -- 1 = victoire, 0 sinon
    IN p_draw       INT,
    IN p_loss       INT,
    IN p_goals_for  INT,
    IN p_goals_against INT
)
BEGIN
    -- créer la ligne si elle n'existe pas encore, sinon mettre à jour
    INSERT INTO stats_joueur (id_user, phase, wins, draws, losses, goals_for, goals_against, total_matches)
    VALUES (p_id_user, p_phase, p_win, p_draw, p_loss, p_goals_for, p_goals_against, 1)
    ON DUPLICATE KEY UPDATE
        wins          = wins          + p_win,
        draws         = draws         + p_draw,
        losses        = losses        + p_loss,
        goals_for     = goals_for     + p_goals_for,
        goals_against = goals_against + p_goals_against,
        total_matches = total_matches + 1;
END$$


-- ------------------------------------------------------------
-- sp_get_player_stats
-- Stats globales d'un joueur pour son profil public
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_player_stats(IN p_id_user INT)
BEGIN
    SELECT
        u.username,
        s.phase,
        s.wins, s.draws, s.losses,
        s.goals_for, s.goals_against,
        (s.goals_for - s.goals_against) AS goal_diff,
        s.total_matches
    FROM   stats_joueur s
    JOIN   users        u ON s.id_user = u.id_user
    WHERE  s.id_user = p_id_user
    ORDER BY s.phase;
END$$


-- ============================================================
-- SECTION 8 — PROCÉDURES : TROPHÉES
-- ============================================================


-- ------------------------------------------------------------
-- sp_award_trophee
-- Attribue un trophée à un joueur (si pas déjà obtenu)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_award_trophee(IN p_id_user INT, IN p_id_trophee INT)
BEGIN
    INSERT IGNORE INTO remporte (id_user, id_trophee)
    VALUES (p_id_user, p_id_trophee);
    -- INSERT IGNORE évite l'erreur de doublon si déjà attribué
END$$


-- ------------------------------------------------------------
-- sp_get_player_trophees
-- Trophées remportés par un joueur (profil public)
-- ------------------------------------------------------------
CREATE PROCEDURE sp_get_player_trophees(IN p_id_user INT)
BEGIN
    SELECT
        t.id_trophee,
        t.nom,
        t.icone,
        r.date_obtention
    FROM   remporte r
    JOIN   trophee  t ON r.id_trophee = t.id_trophee
    WHERE  r.id_user = p_id_user
    ORDER BY r.date_obtention DESC;
END$$


-- ============================================================
-- Remettre le délimiteur standard
-- ============================================================
DELIMITER ;


-- ============================================================
-- RÉFÉRENCE RAPIDE — toutes les procédures et fonctions
-- ============================================================
--
-- FONCTIONS (SELECT fn_xxx(...))
-- ─────────────────────────────────────────────────────────────
-- fn_user_exists_email(email)           → 1/0
-- fn_user_exists_username(username)     → 1/0
-- fn_user_in_cup(id_user, id_cup)       → 1/0
-- fn_cup_is_full(id_cup)                → 1/0
-- fn_aggregate_home(first_leg_id)       → INT (score agrégé home)
-- fn_aggregate_away(first_leg_id)       → INT (score agrégé away)
--
-- PROCÉDURES USERS (CALL sp_xxx(...))
-- ─────────────────────────────────────────────────────────────
-- sp_create_user(email, username, password, OUT id_user)
-- sp_get_user_by_email(email)         → retourne aussi r.nom AS role (JOIN role)
-- sp_get_user_by_id(id_user)          → retourne aussi r.nom AS role (JOIN role)
-- sp_ban_user(id_user, 1|0)
-- sp_set_user_role(id_user, role_nom) → 'player' | 'admin'
--
-- PROCÉDURES CUPS
-- ─────────────────────────────────────────────────────────────
-- sp_get_open_cups()
-- sp_get_cup_detail(id_cup)
-- sp_create_cup(titre, visibilite, join_code, min, max, created_by, OUT id_cup)
-- sp_join_cup(id_user, id_cup, join_code, OUT result)   result: 0=ok 1=plein 2=code invalide 3=déjà inscrit
-- sp_update_cup_statut(id_cup, statut)
-- sp_get_cups_by_user(id_user)
--
-- PROCÉDURES GROUPES
-- ─────────────────────────────────────────────────────────────
-- sp_create_group(nom, id_cup, OUT id_groupe)
-- sp_add_player_to_group(id_user, id_groupe)
-- sp_get_group_standings(id_groupe)
-- sp_update_group_standings(id_match, home_score, away_score)
--
-- PROCÉDURES MATCHS
-- ─────────────────────────────────────────────────────────────
-- sp_create_match(round, leg, id_cup, id_groupe, home, away, first_leg_id, deadline, OUT id_match)
-- sp_get_match_detail(id_match)
-- sp_get_matches_by_user(id_user)
-- sp_get_group_matches(id_groupe)
-- sp_get_disputed_matches()
-- sp_update_match_statut(id_match, statut)
--
-- PROCÉDURES SOUMISSIONS & RÉSULTATS
-- ─────────────────────────────────────────────────────────────
-- sp_submit_score(id_match, id_user, home_score, away_score, OUT status)  status: 'waiting'|'confirmed'|'disputed'
-- sp_resolve_dispute(id_match, home_score, away_score, resolved_by)
-- sp_check_aggregate(leg2_id, OUT winner_id)   winner_id: id_user ou 0 (barrage créé)
--
-- PROCÉDURES STATS
-- ─────────────────────────────────────────────────────────────
-- sp_update_player_stats(id_user, phase, win, draw, loss, goals_for, goals_against)
-- sp_get_player_stats(id_user)
--
-- PROCÉDURES TROPHÉES
-- ─────────────────────────────────────────────────────────────
-- sp_award_trophee(id_user, id_trophee)
-- sp_get_player_trophees(id_user)
-- ============================================================
