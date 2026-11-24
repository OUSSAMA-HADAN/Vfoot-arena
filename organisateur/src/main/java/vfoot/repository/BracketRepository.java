package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Standing;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class BracketRepository {

    private final Connection conn = Database.get();

    // ── Méthodes publiques ────────────────────────────────────────

    // Retourne le tour éliminatoire actuel (ex: "QF", "SF", "F").
    // Retourne null si aucun match knockout n'a encore été créé pour cette cup.
    public String getCurrentRound(int idCup) {
        String sql = """
            SELECT round FROM matche
            WHERE id_cup = ? AND round != 'group'
            ORDER BY FIELD(round, 'R16', 'QF', 'SF', 'F') DESC
            LIMIT 1
            """;
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idCup);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) return rs.getString("round");
        } catch (SQLException e) {
            System.err.println("[BracketRepository] getCurrentRound : " + e.getMessage());
        }
        return null;
    }

    // Retourne true si tous les matchs du tour donné sont confirmés.
    public boolean isRoundComplete(int idCup, String round) {
        String sql = "SELECT COUNT(*) FROM matche WHERE id_cup = ? AND round = ? AND statut != 'confirmed'";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idCup);
            stmt.setString(2, round);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) return rs.getInt(1) == 0;
        } catch (SQLException e) {
            System.err.println("[BracketRepository] isRoundComplete : " + e.getMessage());
        }
        return false;
    }

    // Génère le premier tour éliminatoire à partir du classement des groupes.
    // Croisement : A1 vs B2 et B1 vs A2 (groupes consécutifs se croisent).
    public void generateFirstRound(int idCup, List<Standing> standings, LocalDateTime deadline) {

        // ── Étape 1 : regrouper les joueurs par groupe ────────────
        // standings arrive déjà triée par groupe depuis la DB (ORDER BY g.id_groupe).
        // On parcourt la liste et on démarre une nouvelle sous-liste chaque fois
        // que le nom du groupe change (ex: "A" → "B").
        List<List<Standing>> groupes = new ArrayList<>();
        String dernierGroupe = null;

        for (Standing s : standings) {
            if (!s.getGroupeNom().equals(dernierGroupe)) {
                // Nouveau groupe rencontré → on crée une nouvelle sous-liste vide
                groupes.add(new ArrayList<>());
                dernierGroupe = s.getGroupeNom();
            }
            // On ajoute ce joueur dans la dernière sous-liste créée
            groupes.get(groupes.size() - 1).add(s);
        }

        // ── Étape 2 : déterminer le nom du premier tour ───────────
        String round = determineRound(groupes.size());

        // ── Étape 3 : créer les matchs (croisement A1vsB2, B1vsA2) ─
        // On prend les groupes 2 par 2 : (A et B), puis (C et D), etc.
        for (int i = 0; i < groupes.size() - 1; i += 2) {
            List<Standing> gA = groupes.get(i);
            List<Standing> gB = groupes.get(i + 1);

            int a1 = gA.get(0).getIdUser(); // 1er du groupe A
            int a2 = gA.get(1).getIdUser(); // 2e  du groupe A
            int b1 = gB.get(0).getIdUser(); // 1er du groupe B
            int b2 = gB.get(1).getIdUser(); // 2e  du groupe B

            insererPaire(idCup, round, a1, b2, deadline); // A1 reçoit B2 en leg 1
            insererPaire(idCup, round, b1, a2, deadline); // B1 reçoit A2 en leg 1
        }
    }

    // Génère le tour suivant à partir des vainqueurs du tour actuel.
    // Retourne false si une égalité en agrégat empêche de déterminer un vainqueur.
    public boolean generateNextRound(int idCup, String currentRound, LocalDateTime deadline) {

        // Récupère la liste des vainqueurs (null = égalité détectée)
        List<Integer> vainqueurs = getWinners(idCup, currentRound);
        if (vainqueurs == null) return false;

        String prochainTour = getNextRound(currentRound);

        // On apparie les vainqueurs 2 par 2 dans l'ordre d'arrivée
        for (int i = 0; i < vainqueurs.size() - 1; i += 2) {
            int joueurA = vainqueurs.get(i);
            int joueurB = vainqueurs.get(i + 1);
            insererPaire(idCup, prochainTour, joueurA, joueurB, deadline);
        }

        return true;
    }

    // Retourne le nom du tour suivant : R16 → QF → SF → F
    public String getNextRound(String round) {
        return switch (round) {
            case "R16" -> "QF";
            case "QF"  -> "SF";
            case "SF"  -> "F";
            default    -> "F";
        };
    }

    // ── Méthodes privées ─────────────────────────────────────────

    // Insère une paire de matchs (leg 1 + leg 2) pour deux joueurs.
    // Si c'est la Finale, insère un seul match avec leg='single'.
    // La transaction garantit que les deux legs sont insérés ensemble ou pas du tout.
    private void insererPaire(int idCup, String round, int domicile, int exterieur, LocalDateTime deadline) {
        String sql = """
            INSERT INTO matche (round, leg, statut, deadline, id_cup, id_groupe, first_leg_id, home_user_id, away_user_id)
            VALUES (?, ?, 'pending', ?, ?, NULL, ?, ?, ?)
            """;
        try {
            // Début de la transaction — les deux insertions réussissent ensemble ou échouent ensemble
            conn.setAutoCommit(false);
            PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);

            if (round.equals("F")) {
                // Finale : un seul match, pas de match retour
                stmt.setString(1, round);
                stmt.setString(2, "single");
                stmt.setTimestamp(3, Timestamp.valueOf(deadline));
                stmt.setInt(4, idCup);
                stmt.setNull(5, Types.INTEGER); // pas de first_leg_id pour la finale
                stmt.setInt(6, domicile);
                stmt.setInt(7, exterieur);
                stmt.executeUpdate();

            } else {
                // Leg 1 : le joueur domicile reçoit l'extérieur
                stmt.setString(1, round);
                stmt.setString(2, "1");
                stmt.setTimestamp(3, Timestamp.valueOf(deadline));
                stmt.setInt(4, idCup);
                stmt.setNull(5, Types.INTEGER); // first_leg_id = null pour le leg 1
                stmt.setInt(6, domicile);
                stmt.setInt(7, exterieur);
                stmt.executeUpdate();

                // Récupérer l'ID auto-généré du leg 1 pour le lier au leg 2
                ResultSet cles = stmt.getGeneratedKeys();
                long idLeg1 = cles.next() ? cles.getLong(1) : -1;

                // Leg 2 : les rôles domicile/extérieur sont inversés
                stmt.setString(1, round);
                stmt.setString(2, "2");
                stmt.setTimestamp(3, Timestamp.valueOf(deadline));
                stmt.setInt(4, idCup);
                stmt.setLong(5, idLeg1); // pointe vers le leg 1
                stmt.setInt(6, exterieur); // maintenant domicile
                stmt.setInt(7, domicile);  // maintenant extérieur
                stmt.executeUpdate();
            }

            conn.commit(); // valider les insertions dans la DB
        } catch (SQLException e) {
            try { conn.rollback(); } catch (SQLException ignored) {} // annuler en cas d'erreur
            System.err.println("[BracketRepository] insererPaire : " + e.getMessage());
        } finally {
            try { conn.setAutoCommit(true); } catch (SQLException ignored) {} // toujours rétablir
        }
    }

    // Calcule les vainqueurs d'un tour sur l'agrégat des 2 manches.
    // Agrégat joueur A = buts marqués en leg1 (domicile) + buts marqués en leg2 (extérieur)
    // Retourne null si une paire finit à égalité (barrage nécessaire).
    private List<Integer> getWinners(int idCup, String round) {
        String sql = """
            SELECT
                m1.home_user_id                        AS player_a,
                m1.away_user_id                        AS player_b,
                (r1.home_score + r2.away_score)        AS goals_a,
                (r1.away_score + r2.home_score)        AS goals_b
            FROM matche  m1
            JOIN resultat r1 ON r1.id_match = m1.id_match
            JOIN matche  m2 ON m2.first_leg_id = m1.id_match
            JOIN resultat r2 ON r2.id_match = m2.id_match
            WHERE m1.id_cup = ? AND m1.round = ? AND m1.leg = '1'
            ORDER BY m1.id_match
            """;
        List<Integer> vainqueurs = new ArrayList<>();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idCup);
            stmt.setString(2, round);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                int butsA = rs.getInt("goals_a");
                int butsB = rs.getInt("goals_b");
                if (butsA > butsB)      vainqueurs.add(rs.getInt("player_a")); // A passe
                else if (butsB > butsA) vainqueurs.add(rs.getInt("player_b")); // B passe
                else                    return null; // égalité → barrage nécessaire
            }
        } catch (SQLException e) {
            System.err.println("[BracketRepository] getWinners : " + e.getMessage());
            return null;
        }
        return vainqueurs;
    }

    // Détermine le nom du premier tour selon le nombre de groupes.
    // On multiplie par 2 car chaque groupe envoie 2 qualifiés.
    private String determineRound(int nbGroupes) {
        return switch (nbGroupes * 2) {
            case 2  -> "F";   // 1 groupe → 2 qualifiés → directement finale
            case 4  -> "SF";  // 2 groupes → 4 qualifiés → demi-finales
            case 8  -> "QF";  // 4 groupes → 8 qualifiés → quarts de finale
            case 16 -> "R16"; // 8 groupes → 16 qualifiés → 16es de finale
            default -> "QF";
        };
    }
}
