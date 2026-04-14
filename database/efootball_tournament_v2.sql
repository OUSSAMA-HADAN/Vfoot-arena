-- ============================================================
-- BASE DE DONNÉES : efootball_tournament
-- Version : 3.0
-- Changements v2 : suppression bracket_slot, ajout avance_vers sur matche,
--                  ajout id_cup sur matche, ajout trophee et remporte
-- Changements v3 : role séparé en table dédiée (normalisation)
--                  users.role ENUM → users.id_role FK → role.id_role
-- ============================================================
CREATE DATABASE efootball_tournament
  CHARACTER SET utf8mb4                -- support complet de l'unicode (accents, arabes, emojis...)
  COLLATE utf8mb4_unicode_ci;          -- règles de tri insensibles à la casse (ci = case insensitive)

USE efootball_tournament;              -- sélectionne la base pour toutes les prochaines commandes


-- ============================================================
-- TABLE : role
-- Description : rôles globaux disponibles sur la plateforme
--               séparé de users pour faciliter les jointures
--               et permettre d'ajouter des rôles sans ALTER TABLE
-- Ordre       : doit être créé AVANT users (FK users → role)
-- ============================================================
CREATE TABLE role (

  -- Colonnes
  id_role INT         NOT NULL AUTO_INCREMENT,                            -- identifiant unique
  nom     VARCHAR(50) NOT NULL,                                           -- nom du rôle : 'admin', 'player'...

  -- Contraintes
  CONSTRAINT pk_role     PRIMARY KEY (id_role),
  CONSTRAINT uq_role_nom UNIQUE (nom)                                     -- nom de rôle unique

);

-- Données initiales — les deux rôles de base de la plateforme
-- id_role 1 = player (défaut pour tout nouvel utilisateur)
-- id_role 2 = admin
INSERT INTO role (nom) VALUES ('player'), ('admin');


-- ============================================================
-- TABLE : users
-- Description : comptes utilisateurs de la plateforme
-- ============================================================
CREATE TABLE users (

  -- Colonnes
  id_user    INT          NOT NULL AUTO_INCREMENT,                        -- identifiant unique auto-généré
  email      VARCHAR(100) NOT NULL,                                       -- adresse email de connexion
  password   VARCHAR(255) NOT NULL,                                       -- hash du mot de passe (bcrypt)
  username   VARCHAR(50)  NOT NULL,                                       -- nom affiché publiquement
  id_role    INT          NOT NULL DEFAULT 1,                             -- FK vers role (1 = player par défaut)
  is_banned  BOOLEAN      NOT NULL DEFAULT FALSE,                         -- compte banni ou non
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,             -- date d'inscription

  -- Contraintes
  -- NOTE MySQL : le nom des contraintes PRIMARY KEY est ignoré (toujours 'PRIMARY' en interne) — warning 1280 normal
  CONSTRAINT pk_users          PRIMARY KEY (id_user),                     -- clé primaire
  CONSTRAINT fk_users_id_role  FOREIGN KEY (id_role) REFERENCES role(id_role),
  CONSTRAINT uq_users_email    UNIQUE (email),                            -- email unique
  CONSTRAINT uq_users_username UNIQUE (username)                          -- username unique

);


-- ============================================================
-- TABLE : cup
-- Description : tournoi créé par un utilisateur
-- ============================================================
CREATE TABLE cup (

  -- Colonnes
  id_cup        INT          NOT NULL AUTO_INCREMENT,                     -- identifiant unique
  titre         VARCHAR(100) NOT NULL,                                    -- nom de la cup
  visibilite    ENUM('public', 'private') NOT NULL DEFAULT 'public',      -- accès public ou privé
  join_code     VARCHAR(20)  NULL,                                        -- code d'invitation (privé uniquement)
  min_players   INT          NOT NULL DEFAULT 8,                          -- minimum de joueurs requis
  max_players   INT          NOT NULL DEFAULT 64,                         -- maximum de joueurs autorisés
  statut        ENUM('draft','open','started','finished','cancelled') NOT NULL DEFAULT 'draft',
  qualification INT          NOT NULL DEFAULT 2,                          -- places qualificatives par groupe
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,          -- date de création
  created_by    INT          NOT NULL,                                    -- organisateur (FK vers users)

  -- Contraintes
  CONSTRAINT pk_cup            PRIMARY KEY (id_cup),
  CONSTRAINT fk_cup_created_by FOREIGN KEY (created_by)  REFERENCES users(id_user),
  CONSTRAINT ck_cup_players    CHECK (min_players >= 8 AND max_players <= 64),  -- bornes du système
  CONSTRAINT ck_cup_minmax     CHECK (min_players <= max_players),              -- min ne dépasse pas max
  CONSTRAINT ck_cup_joincode   CHECK (                                                        -- exclusivité stricte
    (visibilite = 'public'  AND join_code IS NULL) OR
    (visibilite = 'private' AND join_code IS NOT NULL)
  )

);


-- ============================================================
-- TABLE : groupe
-- Description : groupe de joueurs au sein d'une cup
-- ============================================================
CREATE TABLE groupe (

  -- Colonnes
  id_groupe INT         NOT NULL AUTO_INCREMENT,                          -- identifiant unique
  nom       CHAR(1)     NOT NULL,                                         -- lettre du groupe : 'A', 'B', 'C'... (le frontend affiche "Groupe A")
  id_cup    INT         NOT NULL,                                         -- cup à laquelle appartient ce groupe

  -- Contraintes
  CONSTRAINT pk_groupe         PRIMARY KEY (id_groupe),
  CONSTRAINT fk_groupe_id_cup  FOREIGN KEY (id_cup) REFERENCES cup(id_cup),
  CONSTRAINT uq_groupe_nom_cup UNIQUE (nom, id_cup),                      -- lettre unique par cup
  CONSTRAINT ck_groupe_nom     CHECK (nom BETWEEN 'A' AND 'Z')            -- uniquement lettres majuscules

);


-- ============================================================
-- TABLE : matche
-- Description : match entre deux joueurs dans une cup
-- Changements v2 : ajout id_cup (possède) et next_match_id (avance vers)
-- ============================================================
CREATE TABLE matche (

  -- Colonnes
  id_match      INT      NOT NULL AUTO_INCREMENT,                         -- identifiant unique
  round         ENUM('group','R16','QF','SF','F') NOT NULL,               -- phase du match
  leg           ENUM('1','2','3','single') NOT NULL DEFAULT 'single',     -- manche : aller / retour / barrage / match unique
  statut        ENUM('pending','submitted','disputed','confirmed','forfeited') NOT NULL DEFAULT 'pending',
  deadline      DATETIME NULL,                                            -- date limite de soumission (optionnelle)
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,              -- date de création du match
  id_cup        INT      NOT NULL,                                        -- cup à laquelle appartient ce match
  id_groupe     INT      NULL,                                            -- NULL si match knockout
  first_leg_id  INT      NULL,                                            -- leg 2 uniquement : pointe vers le leg 1 du même duel
  next_match_id INT      NULL,                                            -- leg 2 uniquement : match du tour suivant (NULL si finale)
  home_user_id  INT      NOT NULL,                                        -- joueur domicile (inversé entre leg 1 et leg 2)
  away_user_id  INT      NOT NULL,                                        -- joueur extérieur

  -- Contraintes
  CONSTRAINT pk_matche               PRIMARY KEY (id_match),
  CONSTRAINT fk_matche_id_cup        FOREIGN KEY (id_cup)         REFERENCES cup(id_cup),
  CONSTRAINT fk_matche_id_groupe     FOREIGN KEY (id_groupe)      REFERENCES groupe(id_groupe),
  CONSTRAINT fk_matche_first_leg     FOREIGN KEY (first_leg_id)   REFERENCES matche(id_match), -- auto-référence aller→retour
  CONSTRAINT fk_matche_next_match    FOREIGN KEY (next_match_id)  REFERENCES matche(id_match), -- auto-référence avance vers
  CONSTRAINT fk_matche_home_user     FOREIGN KEY (home_user_id)   REFERENCES users(id_user),
  CONSTRAINT fk_matche_away_user     FOREIGN KEY (away_user_id)   REFERENCES users(id_user),
  CONSTRAINT ck_matche_diff_users    CHECK (home_user_id <> away_user_id),                     -- pas de match contre soi-même
  CONSTRAINT ck_matche_round_leg     CHECK (                                                   -- cohérence phase / manche
    (round IN ('R16','QF','SF') AND leg IN ('1','2','3')) OR                                   -- knockout : aller + retour + barrage éventuel
    (round = 'F'                AND leg = 'single')       OR                                   -- finale : match unique
    (round = 'group'            AND leg IN ('1','2'))                                          -- groupes : aller + retour, matchs indépendants
  ),
  CONSTRAINT ck_matche_groupe_round  CHECK (                                                   -- cohérence groupe / phase
    (round = 'group' AND id_groupe IS NOT NULL) OR
    (round <> 'group' AND id_groupe IS NULL)
  ),
  CONSTRAINT ck_matche_first_leg     CHECK (                                                   -- first_leg_id obligatoire pour leg 2 et leg 3 knockout uniquement
    (leg IN ('2','3') AND round IN ('R16','QF','SF')) = (first_leg_id IS NOT NULL)             -- groupe leg 2 → NULL (indépendants) | barrage → pointe vers leg 1
  )

);


-- ============================================================
-- TABLE : submission
-- Description : score soumis par un joueur pour un match
-- ============================================================
CREATE TABLE submission (

  -- Colonnes
  id_submission   INT      NOT NULL AUTO_INCREMENT,                       -- identifiant unique
  home_score      INT      NOT NULL,                                      -- score soumis pour le joueur domicile
  away_score      INT      NOT NULL,                                      -- score soumis pour le joueur extérieur
  date_soumission DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,            -- date de soumission
  id_match        INT      NOT NULL,                                      -- match concerné
  id_user         INT      NOT NULL,                                      -- joueur qui soumet

  -- Contraintes
  CONSTRAINT pk_submission            PRIMARY KEY (id_submission),
  CONSTRAINT fk_submission_id_match   FOREIGN KEY (id_match) REFERENCES matche(id_match),
  CONSTRAINT fk_submission_id_user    FOREIGN KEY (id_user)  REFERENCES users(id_user),
  CONSTRAINT uq_submission_user_match UNIQUE (id_match, id_user),         -- une seule soumission par joueur par match
  CONSTRAINT ck_submission_scores     CHECK (home_score >= 0 AND away_score >= 0) -- scores positifs uniquement

);


-- ============================================================
-- TABLE : resultat
-- Description : score final confirmé d'un match
-- ============================================================
CREATE TABLE resultat (

  -- Colonnes
  id_resultat       INT      NOT NULL AUTO_INCREMENT,                     -- identifiant unique
  home_score        INT      NOT NULL,                                    -- score final joueur domicile
  away_score        INT      NOT NULL,                                    -- score final joueur extérieur
  date_confirmation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,          -- date de confirmation
  id_match          INT      NOT NULL,                                    -- match concerné
  confirmed_by      INT      NULL,                                        -- NULL = confirmé automatiquement | NOT NULL = organisateur a tranché une dispute

  -- Contraintes
  CONSTRAINT pk_resultat           PRIMARY KEY (id_resultat),
  CONSTRAINT fk_resultat_id_match  FOREIGN KEY (id_match)     REFERENCES matche(id_match),
  CONSTRAINT fk_resultat_confirmed FOREIGN KEY (confirmed_by) REFERENCES users(id_user),
  CONSTRAINT uq_resultat_match     UNIQUE (id_match),                     -- un seul résultat confirmé par match
  CONSTRAINT ck_resultat_scores    CHECK (home_score >= 0 AND away_score >= 0) -- scores positifs uniquement

);


-- ============================================================
-- TABLE : participe
-- Description : liaison entre un utilisateur et une cup
--               avec son rôle dans cette cup
-- ============================================================
CREATE TABLE participe (

  -- Colonnes
  id_user       INT      NOT NULL,                                        -- joueur
  id_cup        INT      NOT NULL,                                        -- cup concernée
  role          ENUM('organizer', 'player') NOT NULL DEFAULT 'player',    -- rôle dans cette cup (ENUM suffit, pas besoin de CHECK)
  date_adhesion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,              -- date d'inscription à la cup

  -- Contraintes
  CONSTRAINT pk_participe         PRIMARY KEY (id_user, id_cup),          -- clé primaire composite
  CONSTRAINT fk_participe_id_user FOREIGN KEY (id_user) REFERENCES users(id_user),
  CONSTRAINT fk_participe_id_cup  FOREIGN KEY (id_cup)  REFERENCES cup(id_cup)
  -- NOTE : ck_participe_role supprimé — le ENUM('organizer','player') l'applique déjà nativement

);


-- ============================================================
-- TABLE : est_classe_dans
-- Description : classement d'un joueur dans un groupe
--               mis à jour après chaque match confirmé
-- ============================================================
CREATE TABLE est_classe_dans (

  -- Colonnes
  id_user       INT NOT NULL,                                             -- joueur
  id_groupe     INT NOT NULL,                                             -- groupe concerné
  played        INT NOT NULL DEFAULT 0,                                   -- matchs joués
  wins          INT NOT NULL DEFAULT 0,                                   -- victoires
  draws         INT NOT NULL DEFAULT 0,                                   -- matchs nuls
  losses        INT NOT NULL DEFAULT 0,                                   -- défaites
  goals_for     INT NOT NULL DEFAULT 0,                                   -- buts marqués
  goals_against INT NOT NULL DEFAULT 0,                                   -- buts encaissés
  points        INT NOT NULL DEFAULT 0,                                   -- points totaux

  -- Contraintes
  CONSTRAINT pk_est_classe_dans          PRIMARY KEY (id_user, id_groupe), -- clé primaire composite
  CONSTRAINT fk_est_classe_id_user       FOREIGN KEY (id_user)   REFERENCES users(id_user),
  CONSTRAINT fk_est_classe_id_groupe     FOREIGN KEY (id_groupe) REFERENCES groupe(id_groupe),
  CONSTRAINT ck_est_classe_played        CHECK (played >= 0),
  CONSTRAINT ck_est_classe_wins          CHECK (wins >= 0),
  CONSTRAINT ck_est_classe_draws         CHECK (draws >= 0),
  CONSTRAINT ck_est_classe_losses        CHECK (losses >= 0),
  CONSTRAINT ck_est_classe_goals_for     CHECK (goals_for >= 0),
  CONSTRAINT ck_est_classe_goals_against CHECK (goals_against >= 0),
  CONSTRAINT ck_est_classe_points        CHECK (points >= 0),
  CONSTRAINT ck_est_classe_coherence     CHECK (played = wins + draws + losses) -- cohérence des stats

);


-- ============================================================
-- TABLE : stats_joueur
-- Description : statistiques globales d'un joueur
--               séparées par phase (groupes ou knockout)
-- ============================================================
CREATE TABLE stats_joueur (

  -- Colonnes
  id_stats      INT  NOT NULL AUTO_INCREMENT,                             -- identifiant unique
  id_user       INT  NOT NULL,                                            -- joueur concerné
  phase         ENUM('group', 'knockout') NOT NULL,                       -- phase de jeu
  wins          INT  NOT NULL DEFAULT 0,                                  -- victoires
  draws         INT  NOT NULL DEFAULT 0,                                  -- matchs nuls
  losses        INT  NOT NULL DEFAULT 0,                                  -- défaites
  goals_for     INT  NOT NULL DEFAULT 0,                                  -- buts marqués
  goals_against INT  NOT NULL DEFAULT 0,                                  -- buts encaissés
  total_matches INT  NOT NULL DEFAULT 0,                                  -- total matchs joués

  -- Contraintes
  CONSTRAINT pk_stats_joueur               PRIMARY KEY (id_stats),
  CONSTRAINT fk_stats_joueur_id_user       FOREIGN KEY (id_user) REFERENCES users(id_user),
  CONSTRAINT uq_stats_joueur_user_phase    UNIQUE (id_user, phase),       -- une seule ligne par joueur par phase
  CONSTRAINT ck_stats_joueur_wins          CHECK (wins >= 0),
  CONSTRAINT ck_stats_joueur_draws         CHECK (draws >= 0),
  CONSTRAINT ck_stats_joueur_losses        CHECK (losses >= 0),
  CONSTRAINT ck_stats_joueur_goals_for     CHECK (goals_for >= 0),
  CONSTRAINT ck_stats_joueur_goals_against CHECK (goals_against >= 0),
  CONSTRAINT ck_stats_joueur_total         CHECK (total_matches >= 0),
  CONSTRAINT ck_stats_joueur_coherence     CHECK (total_matches = wins + draws + losses) -- cohérence des stats

);


-- ============================================================
-- TABLE : trophee
-- Description : trophées disponibles sur la plateforme
-- ============================================================
CREATE TABLE trophee (

  -- Colonnes
  id_trophee INT          NOT NULL AUTO_INCREMENT,                        -- identifiant unique
  nom        VARCHAR(100) NOT NULL,                                       -- nom du trophée
  icone      VARCHAR(255) NULL,                                           -- chemin vers l'icône du trophée

  -- Contraintes
  CONSTRAINT pk_trophee     PRIMARY KEY (id_trophee),
  CONSTRAINT uq_trophee_nom UNIQUE (nom)                                  -- nom de trophée unique

);


-- ============================================================
-- TABLE : remporte
-- Description : liaison entre un utilisateur et un trophée
--               avec la date d'obtention
-- ============================================================
CREATE TABLE remporte (

  -- Colonnes
  id_user        INT      NOT NULL,                                       -- joueur
  id_trophee     INT      NOT NULL,                                       -- trophée remporté
  date_obtention DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,             -- date d'obtention du trophée

  -- Contraintes
  CONSTRAINT pk_remporte            PRIMARY KEY (id_user, id_trophee),    -- clé primaire composite
  CONSTRAINT fk_remporte_id_user    FOREIGN KEY (id_user)    REFERENCES users(id_user),
  CONSTRAINT fk_remporte_id_trophee FOREIGN KEY (id_trophee) REFERENCES trophee(id_trophee)

);
