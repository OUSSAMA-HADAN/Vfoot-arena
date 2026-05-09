package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Cup;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CupRepository {

    private final Connection conn = Database.get();

    public List<Cup> findAll() throws SQLException {
        List<Cup> list = new ArrayList<>();
        String sql = "SELECT id_cup, titre, statut, visibilite, min_players, max_players FROM cup ORDER BY id_cup DESC";
        try (PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                list.add(map(rs));
            }
        }
        return list;
    }

    public Cup findById(int id) throws SQLException {
        String sql = "SELECT id_cup, titre, statut, visibilite, min_players, max_players FROM cup WHERE id_cup = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? map(rs) : null;
            }
        }
    }

    /**
     * Crée une nouvelle cup publique (sans join code).
     * Retourne l'id généré.
     */
    public int create(String titre, int minPlayers, int maxPlayers, int createdBy) throws SQLException {
        String sql = """
                INSERT INTO cup (titre, visibilite, join_code, min_players, max_players, statut, created_by)
                VALUES (?, 'public', NULL, ?, ?, 'pending', ?)
                """;
        try (PreparedStatement ps = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setString(1, titre);
            ps.setInt(2, minPlayers);
            ps.setInt(3, maxPlayers);
            ps.setInt(4, createdBy);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) return keys.getInt(1);
            }
        }
        throw new SQLException("Aucune clé générée pour la cup");
    }

    public void updateStatut(int cupId, String statut) throws SQLException {
        String sql = "UPDATE cup SET statut = ? WHERE id_cup = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, statut);
            ps.setInt(2, cupId);
            ps.executeUpdate();
        }
    }

    // ── stat rapide pour le dashboard ────────────────────────────────────────
    public int countByStatut(String statut) throws SQLException {
        String sql = "SELECT COUNT(*) FROM cup WHERE statut = ?";
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, statut);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? rs.getInt(1) : 0;
            }
        }
    }

    // ── mapping ResultSet → Cup ───────────────────────────────────────────────
    private Cup map(ResultSet rs) throws SQLException {
        return new Cup(
                rs.getInt("id_cup"),
                rs.getString("titre"),
                rs.getString("statut"),
                rs.getString("visibilite"),
                rs.getInt("min_players"),
                rs.getInt("max_players")
        );
    }
}
