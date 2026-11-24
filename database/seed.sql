-- ============================================================
-- SEED : efootball_tournament
-- 8 utilisateurs · 3 cups · mot de passe : 00000000
-- ============================================================

USE efootball_tournament;

-- ── 1. TROPHÉES ──────────────────────────────────────────────
INSERT INTO trophee (nom, icone) VALUES
  ('Champion VFoot', 'trophy_gold.png'),
  ('Finaliste',      'trophy_silver.png');

-- ── 2. UTILISATEURS ─────────────────────────────────────────
INSERT INTO users (email, password, username, id_role) VALUES
  ('admin@vfoot.com', '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'Admin',        2),
  ('org@vfoot.com',   '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'OrganisateurX',1),
  ('p1@vfoot.com',    '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'Striker_X',    1),
  ('p2@vfoot.com',    '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'CoolKeeper',   1),
  ('p3@vfoot.com',    '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'TikiTaka',     1),
  ('p4@vfoot.com',    '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'WallFC',       1),
  ('p5@vfoot.com',    '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'NightWolf',    1),
  ('p6@vfoot.com',    '$2y$10$fHagP110EPzaFe4Gp6INw.7byEvW8EGnzf0dvZMG4IVFPUT3jHiJ6', 'SwiftBlade',   1);
-- Admin=1, Org=2, Striker=3, CoolKeeper=4, TikiTaka=5, WallFC=6, NightWolf=7, SwiftBlade=8

-- ── 3. CUPS ──────────────────────────────────────────────────
INSERT INTO cup (titre, visibilite, statut, qualification, min_players, max_players, created_by) VALUES
  ('VFoot S1 — Terminée',      'public', 'finished', 2, 8, 32, 2),
  ('VFoot S2 — Knockout',      'public', 'started',  2, 8, 32, 2),
  ('VFoot S3 — Phase Groupes', 'public', 'started',  2, 8, 32, 2);
-- S1=1, S2=2, S3=3

-- ── 4. GROUPES ──────────────────────────────────────────────
INSERT INTO groupe (nom, id_cup) VALUES
  ('A', 1), ('B', 1),   -- Cup 1 : id_groupe 1, 2
  ('A', 2), ('B', 2),   -- Cup 2 : id_groupe 3, 4
  ('A', 3), ('B', 3);   -- Cup 3 : id_groupe 5, 6

-- ── 5. PARTICIPATIONS ───────────────────────────────────────
INSERT INTO participe (id_user, id_cup, role) VALUES
  (2,1,'organizer'), (3,1,'player'), (4,1,'player'), (5,1,'player'), (6,1,'player');

INSERT INTO participe (id_user, id_cup, role) VALUES
  (2,2,'organizer'), (5,2,'player'), (6,2,'player'), (7,2,'player'), (8,2,'player');

INSERT INTO participe (id_user, id_cup, role) VALUES
  (2,3,'organizer'),
  (3,3,'player'), (4,3,'player'), (5,3,'player'),
  (6,3,'player'), (7,3,'player'), (8,3,'player');


-- ============================================================
-- CUP 1 — TERMINÉE
-- Gr.A : Striker_X(3) vs CoolKeeper(4)    → A1=Striker, A2=CoolKeeper
-- Gr.B : TikiTaka(5) vs WallFC(6)         → B1=TikiTaka, B2=WallFC
-- SF1 : Striker(A1) vs WallFC(B2)         → Striker passe (agg 4-1)
-- SF2 : TikiTaka(B1) vs CoolKeeper(A2)    → TikiTaka passe (agg 5-1)
-- Finale : Striker_X 2-1 TikiTaka         → STRIKER_X CHAMPION
-- ============================================================

-- Groupe A
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',1,1,3,4);
SET @c1_ga = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,1,@c1_ga,3),(2,1,@c1_ga,4);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,1,@c1_ga,NULL);

-- Groupe B
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',1,2,5,6);
SET @c1_gb = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,0,@c1_gb,5),(2,0,@c1_gb,6);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,0,@c1_gb,NULL);

-- Classement groupe Cup 1
INSERT INTO est_classe_dans (id_user,id_cup,phase,id_groupe,played,wins,draws,losses,goals_for,goals_against,points) VALUES
  (3,1,'group',1, 1,1,0,0, 2,1,3),
  (4,1,'group',1, 1,0,0,1, 1,2,0),
  (5,1,'group',2, 1,1,0,0, 2,0,3),
  (6,1,'group',2, 1,0,0,1, 0,2,0);

-- SF1 leg1 : Striker(3) dom vs WallFC(6) ext → 2-1
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('SF','1','confirmed',1,NULL,3,6);
SET @c1_sf1l1 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,1,@c1_sf1l1,3),(2,1,@c1_sf1l1,6);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,1,@c1_sf1l1,NULL);

-- SF1 leg2 : WallFC(6) dom vs Striker(3) ext → 0-2
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,first_leg_id,home_user_id,away_user_id)
  VALUES ('SF','2','confirmed',1,NULL,@c1_sf1l1,6,3);
SET @c1_sf1l2 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (0,2,@c1_sf1l2,6),(0,2,@c1_sf1l2,3);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (0,2,@c1_sf1l2,NULL);
-- agg Striker : 2+2=4 | WallFC : 1+0=1 → Striker passe

-- SF2 leg1 : TikiTaka(5) dom vs CoolKeeper(4) ext → 3-0
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('SF','1','confirmed',1,NULL,5,4);
SET @c1_sf2l1 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (3,0,@c1_sf2l1,5),(3,0,@c1_sf2l1,4);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (3,0,@c1_sf2l1,NULL);

-- SF2 leg2 : CoolKeeper(4) dom vs TikiTaka(5) ext → 1-2
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,first_leg_id,home_user_id,away_user_id)
  VALUES ('SF','2','confirmed',1,NULL,@c1_sf2l1,4,5);
SET @c1_sf2l2 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (1,2,@c1_sf2l2,4),(1,2,@c1_sf2l2,5);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (1,2,@c1_sf2l2,NULL);
-- agg TikiTaka : 3+2=5 | CoolKeeper : 0+1=1 → TikiTaka passe

-- Finale : Striker_X(3) vs TikiTaka(5) → 2-1
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('F','single','confirmed',1,NULL,3,5);
SET @c1_final = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,1,@c1_final,3),(2,1,@c1_final,5);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,1,@c1_final,NULL);

-- Classement knockout Cup 1
INSERT INTO est_classe_dans (id_user,id_cup,phase,id_groupe,played,wins,draws,losses,goals_for,goals_against,points) VALUES
  (3,1,'knockout',NULL, 3,3,0,0, 6,2,0),
  (4,1,'knockout',NULL, 2,0,0,2, 1,5,0),
  (5,1,'knockout',NULL, 3,2,0,1, 6,3,0),
  (6,1,'knockout',NULL, 2,0,0,2, 1,4,0);

-- Trophées Cup 1
INSERT INTO remporte (id_user,id_trophee) VALUES (3,1),(5,2);


-- ============================================================
-- CUP 2 — PHASE KNOCKOUT EN COURS
-- Gr.A : TikiTaka(5) vs WallFC(6)          → A1=TikiTaka, A2=WallFC
-- Gr.B : NightWolf(7) vs SwiftBlade(8)     → B1=NightWolf, B2=SwiftBlade
-- SF1 : TikiTaka(A1) vs SwiftBlade(B2)     → leg1 submitted (TikiTaka a soumis), leg2 pending
-- SF2 : NightWolf(B1) vs WallFC(A2)        → leg1 pending, leg2 pending
-- ============================================================

-- Groupe A
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',2,3,5,6);
SET @c2_ga = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,1,@c2_ga,5),(2,1,@c2_ga,6);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,1,@c2_ga,NULL);

-- Groupe B
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',2,4,7,8);
SET @c2_gb = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (3,0,@c2_gb,7),(3,0,@c2_gb,8);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (3,0,@c2_gb,NULL);

-- Classement groupe Cup 2
INSERT INTO est_classe_dans (id_user,id_cup,phase,id_groupe,played,wins,draws,losses,goals_for,goals_against,points) VALUES
  (5,2,'group',3, 1,1,0,0, 2,1,3),
  (6,2,'group',3, 1,0,0,1, 1,2,0),
  (7,2,'group',4, 1,1,0,0, 3,0,3),
  (8,2,'group',4, 1,0,0,1, 0,3,0);

-- SF1 leg1 : TikiTaka(5) dom vs SwiftBlade(8) ext — submitted (TikiTaka a soumis, SwiftBlade non)
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('SF','1','submitted',2,NULL,5,8);
SET @c2_sf1l1 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,0,@c2_sf1l1,5);

-- SF1 leg2 — pending
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,first_leg_id,home_user_id,away_user_id)
  VALUES ('SF','2','pending',2,NULL,@c2_sf1l1,8,5);

-- SF2 leg1 : NightWolf(7) dom vs WallFC(6) ext — pending
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('SF','1','pending',2,NULL,7,6);
SET @c2_sf2l1 = LAST_INSERT_ID();

-- SF2 leg2 — pending
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,first_leg_id,home_user_id,away_user_id)
  VALUES ('SF','2','pending',2,NULL,@c2_sf2l1,6,7);


-- ============================================================
-- CUP 3 — PHASE DE GROUPES (1 MATCH RESTANT)
-- Gr.A : Striker_X(3), TikiTaka(5), NightWolf(7)    — 3/3 confirmés
-- Gr.B : CoolKeeper(4), WallFC(6), SwiftBlade(8)    — 2/3 confirmés
-- Match restant : WallFC(6) vs SwiftBlade(8)
-- ============================================================

-- Groupe A — Striker_X(3) vs TikiTaka(5) → 2-1
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',3,5,3,5);
SET @c3_a1 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,1,@c3_a1,3),(2,1,@c3_a1,5);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,1,@c3_a1,NULL);

-- Groupe A — Striker_X(3) vs NightWolf(7) → 1-0
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',3,5,3,7);
SET @c3_a2 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (1,0,@c3_a2,3),(1,0,@c3_a2,7);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (1,0,@c3_a2,NULL);

-- Groupe A — TikiTaka(5) vs NightWolf(7) → 0-0
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',3,5,5,7);
SET @c3_a3 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (0,0,@c3_a3,5),(0,0,@c3_a3,7);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (0,0,@c3_a3,NULL);

-- Groupe B — CoolKeeper(4) vs WallFC(6) → 3-1
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',3,6,4,6);
SET @c3_b1 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (3,1,@c3_b1,4),(3,1,@c3_b1,6);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (3,1,@c3_b1,NULL);

-- Groupe B — CoolKeeper(4) vs SwiftBlade(8) → 2-0
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','confirmed',3,6,4,8);
SET @c3_b2 = LAST_INSERT_ID();
INSERT INTO submission (home_score,away_score,id_match,id_user) VALUES (2,0,@c3_b2,4),(2,0,@c3_b2,8);
INSERT INTO resultat   (home_score,away_score,id_match,confirmed_by) VALUES (2,0,@c3_b2,NULL);

-- Groupe B — WallFC(6) vs SwiftBlade(8) → EN ATTENTE (le dernier match)
INSERT INTO matche (round,leg,statut,id_cup,id_groupe,home_user_id,away_user_id)
  VALUES ('group','1','pending',3,6,6,8);

-- Classement groupe Cup 3
INSERT INTO est_classe_dans (id_user,id_cup,phase,id_groupe,played,wins,draws,losses,goals_for,goals_against,points) VALUES
  -- Groupe A (3/3 joués)
  (3,3,'group',5, 2,2,0,0, 3,1,6),  -- Striker_X  A1
  (5,3,'group',5, 2,0,1,1, 1,2,1),  -- TikiTaka   A3
  (7,3,'group',5, 2,0,1,1, 0,1,1),  -- NightWolf  A3
  -- Groupe B (2/3 joués, WallFC vs SwiftBlade pending)
  (4,3,'group',6, 2,2,0,0, 5,1,6),  -- CoolKeeper B1
  (6,3,'group',6, 1,0,0,1, 1,3,0),  -- WallFC     B?
  (8,3,'group',6, 1,0,0,1, 0,2,0);  -- SwiftBlade B?
