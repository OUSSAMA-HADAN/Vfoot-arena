package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Qualifier;

import java.sql.*;
import java.util.*;

public class KnockoutRepository {

    private final Connection conn = Database.get();

    // ── Vérifie que tous les matchs de groupe sont confirmés ─────────────────
    public boolean allGroupMatchesConfirmed(int cupId) throws SQLException {
        String sql = """
                SELECT COUNT(*) FROM matche
                WHERE id_cup = ? AND round = 'group'
                  AND statut NOT IN ('confirmed', 'forfeited')
                """;
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() && rs.getInt(1) == 0;
            }
        }
    }

    // ── Retourne le nombre de qualifiés par groupe pour cette cup ─────────────
    public int getQualificationCount(int cupId) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(
                "SELECT qualification FROM cup WHERE id_cup = ?")) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? rs.getInt(1) : 2;
            }
        }
    }

    // ── Retourne les classements de groupe, groupés par lettre ───────────────
    // Map<"A", [Qualifier1, Qualifier2, ...]> trié points desc, goal_diff desc
    public Map<String, List<Qualifier>> getStandingsByGroup(int cupId) throws SQLException {
        String sql = """
                SELECT e.id_user, u.username, g.nom AS groupe_nom,
                       e.points, (e.goals_for - e.goals_against) AS goal_diff
                FROM est_classe_dans e
                JOIN groupe g ON e.id_groupe = g.id_groupe
                JOIN users  u ON e.id_user   = u.id_user
                WHERE g.id_cup = ?
                ORDER BY g.nom, e.points DESC,
                         (e.goals_for - e.goals_against) DESC, e.goals_for DESC
                """;
        Map<String, List<Qualifier>> result = new LinkedHashMap<>();
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    String groupName = rs.getString("groupe_nom");
                    result.computeIfAbsent(groupName, k -> new ArrayList<>());
                    int rank = result.get(groupName).size() + 1;
                    result.get(groupName).add(new Qualifier(
                            rs.getInt("id_user"),
                            rs.getString("username"),
                            groupName,
                            rs.getInt("points"),
                            rs.getInt("goal_diff"),
                            rank
                    ));
                }
            }
        }
        return result;
    }

    // ── Vérifie qu'un bracket knockout n'existe pas déjà ─────────────────────
    public boolean knockoutAlreadyGenerated(int cupId) throws SQLException {
        String sql = "SELECT COUNT(*) FROM matche WHERE id_cup = ? AND round != 'group'";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() && rs.getInt(1) > 0;
            }
        }
    }

    // ── Génère les matchs du premier round éliminatoire ──────────────────────
    // qualifierIds : liste ordonnée des id_user qualifiés (pairés 0vs1, 2vs3…)
    public void generateBracket(int cupId, List<Integer> qualifierIds) throws SQLException {
        String round = determineFirstRound(qualifierIds.size());
        boolean isFinal = round.equals("F");

        for (int i = 0; i + 1 < qualifierIds.size(); i += 2) {
            int playerA = qualifierIds.get(i);
            int playerB = qualifierIds.get(i + 1);

            if (isFinal) {
                createMatch(cupId, playerA, playerB, round, "single", null);
            } else {
                int leg1Id = createMatch(cupId, playerA, playerB, round, "1", null);
                createMatch(cupId, playerB, playerA, round, "2", leg1Id);
            }
        }
    }

    // ── Vérifie que tous les matchs du round actif sont terminés ─────────────
    public boolean allRoundMatchesConfirmed(int cupId, String round) throws SQLException {
        String sql = """
                SELECT COUNT(*) FROM matche
                WHERE id_cup = ? AND round = ?
                  AND statut NOT IN ('confirmed', 'forfeited')
                """;
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            ps.setString(2, round);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() && rs.getInt(1) == 0;
            }
        }
    }

    // ── Trouve le round knockout actuellement actif ───────────────────────────
    // Retourne null si aucun round knockout en cours
    public String getCurrentKnockoutRound(int cupId) throws SQLException {
        String[] rounds = {"R16", "QF", "SF", "F"};
        for (String round : rounds) {
            String sql = """
                    SELECT COUNT(*) FROM matche
                    WHERE id_cup = ? AND round = ?
                    """;
            try (PreparedStatement ps = conn.prepareStatement(sql)) {
                ps.setInt(1, cupId);
                ps.setString(2, round);
                try (ResultSet rs = ps.executeQuery()) {
                    if (rs.next() && rs.getInt(1) > 0) return round;
                }
            }
        }
        return null;
    }

    // ── Calcule les vainqueurs d'un round terminé ─────────────────────────────
    // Retourne la liste ordonnée des id_user vainqueurs (même ordre que les duels)
    public List<Integer> computeWinners(int cupId, String round) throws SQLException {
        if (round.equals("F")) return computeWinnerFinal(cupId);
        return computeWinnersLegged(cupId, round);
    }

    // ── Crée les matchs du round suivant ─────────────────────────────────────
    public String advanceToNextRound(int cupId, String currentRound, List<Integer> winners) throws SQLException {
        String nextRound = nextRoundName(currentRound);
        boolean isFinal  = nextRound.equals("F");

        for (int i = 0; i + 1 < winners.size(); i += 2) {
            int playerA = winners.get(i);
            int playerB = winners.get(i + 1);
            if (isFinal) {
                createMatch(cupId, playerA, playerB, nextRound, "single", null);
            } else {
                int leg1Id = createMatch(cupId, playerA, playerB, nextRound, "1", null);
                createMatch(cupId, playerB, playerA, nextRound, "2", leg1Id);
            }
        }
        return nextRound;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Méthodes privées
    // ═══════════════════════════════════════════════════════════════════════════

    private int createMatch(int cupId, int homeUserId, int awayUserId,
                            String round, String leg, Integer firstLegId) throws SQLException {
        String sql = """
                INSERT INTO matche (round, leg, statut, id_cup, id_groupe, home_user_id, away_user_id, first_leg_id)
                VALUES (?, ?, 'pending', ?, NULL, ?, ?, ?)
                """;
        try (PreparedStatement ps = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setString(1, round);
            ps.setString(2, leg);
            ps.setInt(3, cupId);
            ps.setInt(4, homeUserId);
            ps.setInt(5, awayUserId);
            if (firstLegId != null) ps.setInt(6, firstLegId);
            else                    ps.setNull(6, Types.INTEGER);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) return keys.getInt(1);
            }
        }
        throw new SQLException("Impossible de créer le match");
    }

    // Vainqueur de la finale (match unique)
    private List<Integer> computeWinnerFinal(int cupId) throws SQLException {
        String sql = """
                SELECT m.home_user_id, m.away_user_id, r.home_score, r.away_score
                FROM matche m
                JOIN resultat r ON r.id_match = m.id_match
                WHERE m.id_cup = ? AND m.round = 'F'
                """;
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    int homeId   = rs.getInt("home_user_id");
                    int awayId   = rs.getInt("away_user_id");
                    int homeGoal = rs.getInt("home_score");
                    int awayGoal = rs.getInt("away_score");
                    int winner   = homeGoal >= awayGoal ? homeId : awayId;
                    return List.of(winner);
                }
            }
        }
        return Collections.emptyList();
    }

    // Vainqueurs d'un round aller-retour (agrégat, puis buts à l'extérieur)
    private List<Integer> computeWinnersLegged(int cupId, String round) throws SQLException {
        String sql = """
                SELECT m1.home_user_id AS player_a,
                       m1.away_user_id AS player_b,
                       r1.home_score   AS a_home,
                       r1.away_score   AS b_away,
                       r2.home_score   AS b_home,
                       r2.away_score   AS a_away
                FROM matche  m1
                JOIN resultat r1 ON r1.id_match = m1.id_match
                JOIN matche  m2 ON m2.first_leg_id = m1.id_match AND m2.leg = '2'
                JOIN resultat r2 ON r2.id_match = m2.id_match
                WHERE m1.id_cup = ? AND m1.round = ? AND m1.leg = '1'
                """;
        List<Integer> winners = new ArrayList<>();
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            ps.setString(2, round);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    int playerA = rs.getInt("player_a");
                    int playerB = rs.getInt("player_b");
                    int aggA = rs.getInt("a_home") + rs.getInt("a_away");
                    int aggB = rs.getInt("b_away") + rs.getInt("b_home");

                    if (aggA > aggB) {
                        winners.add(playerA);
                    } else if (aggB > aggA) {
                        winners.add(playerB);
                    } else {
                        // Égalité agrégat → buts à l'extérieur
                        int awayGoalsA = rs.getInt("a_away"); // A à l'extérieur en leg 2
                        int awayGoalsB = rs.getInt("b_away"); // B à l'extérieur en leg 1
                        if (awayGoalsA > awayGoalsB)      winners.add(playerA);
                        else if (awayGoalsB > awayGoalsA) winners.add(playerB);
                        else                               winners.add(playerA); // domicile leg 1 qualifié si tout égal
                    }
                }
            }
        }
        return winners;
    }

    private String determineFirstRound(int count) {
        if (count >= 16) return "R16";
        if (count >= 8)  return "QF";
        if (count >= 4)  return "SF";
        return "F";
    }

    private String nextRoundName(String current) {
        return switch (current) {
            case "R16" -> "QF";
            case "QF"  -> "SF";
            case "SF"  -> "F";
            default    -> throw new IllegalArgumentException("Pas de round après " + current);
        };
    }
}
