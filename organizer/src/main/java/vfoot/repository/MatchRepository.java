package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Match;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class MatchRepository {

    private final Connection conn = Database.get();

    // ── requête commune avec toutes les jointures ─────────────────────────────
    private static final String BASE_SELECT = """
            SELECT m.id_match, m.round, m.leg, m.statut,
                   h.username AS home_name, a.username AS away_name,
                   r.home_score, r.away_score,
                   sh.home_score AS sub_home_h, sh.away_score AS sub_away_h,
                   sa.home_score AS sub_home_a, sa.away_score AS sub_away_a,
                   c.titre AS cup_titre
            FROM matche m
            JOIN users h  ON m.home_user_id = h.id_user
            JOIN users a  ON m.away_user_id = a.id_user
            JOIN cup   c  ON m.id_cup       = c.id_cup
            LEFT JOIN resultat   r  ON r.id_match = m.id_match
            LEFT JOIN submission sh ON sh.id_match = m.id_match AND sh.id_user = m.home_user_id
            LEFT JOIN submission sa ON sa.id_match = m.id_match AND sa.id_user = m.away_user_id
            """;

    public List<Match> findAll() throws SQLException {
        List<Match> list = new ArrayList<>();
        try (PreparedStatement ps = conn.prepareStatement(BASE_SELECT + " ORDER BY m.id_match DESC");
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public List<Match> findDisputed() throws SQLException {
        List<Match> list = new ArrayList<>();
        String sql = BASE_SELECT + " WHERE m.statut = 'disputed' ORDER BY m.id_match";
        try (PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public Match findById(int id) throws SQLException {
        String sql = BASE_SELECT + " WHERE m.id_match = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? map(rs) : null;
            }
        }
    }

    /**
     * Résolution d'un litige : l'organisateur impose le score final.
     * confirmed_by reste NULL (auto-confirmé) — on peut y mettre l'id orga si besoin.
     */
    public void resolveDispute(int matchId, int homeScore, int awayScore) throws SQLException {
        // Supprimer l'ancien résultat s'il existe
        try (PreparedStatement ps = conn.prepareStatement(
                "DELETE FROM resultat WHERE id_match = ?")) {
            ps.setInt(1, matchId);
            ps.executeUpdate();
        }
        // Insérer le score correct
        try (PreparedStatement ps = conn.prepareStatement(
                "INSERT INTO resultat (home_score, away_score, id_match, confirmed_by) VALUES (?,?,?,NULL)")) {
            ps.setInt(1, homeScore);
            ps.setInt(2, awayScore);
            ps.setInt(3, matchId);
            ps.executeUpdate();
        }
        // Passer le match en confirmed
        try (PreparedStatement ps = conn.prepareStatement(
                "UPDATE matche SET statut='confirmed' WHERE id_match = ?")) {
            ps.setInt(1, matchId);
            ps.executeUpdate();
        }
    }

    public int countByStatut(String statut) throws SQLException {
        String sql = "SELECT COUNT(*) FROM matche WHERE statut = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, statut);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? rs.getInt(1) : 0;
            }
        }
    }

    // ── mapping ResultSet → Match ─────────────────────────────────────────────
    private Match map(ResultSet rs) throws SQLException {
        return new Match(
                rs.getInt("id_match"),
                rs.getString("round"),
                rs.getString("leg"),
                rs.getString("statut"),
                rs.getString("home_name"),
                rs.getString("away_name"),
                (Integer) rs.getObject("home_score"),
                (Integer) rs.getObject("away_score"),
                (Integer) rs.getObject("sub_home_h"),
                (Integer) rs.getObject("sub_away_h"),
                (Integer) rs.getObject("sub_home_a"),
                (Integer) rs.getObject("sub_away_a"),
                rs.getString("cup_titre")
        );
    }
}
