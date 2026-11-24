package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Organisateur;

import java.sql.*;

public class AuthRepository {

    private final Connection conn = Database.get();

    public Organisateur findByEmail(String email) {
        String sql = """
            SELECT u.id_user, u.email, u.username, u.password AS password_hash
            FROM users u
            WHERE u.email = ?
              AND u.is_banned = false
              AND EXISTS (SELECT 1 FROM cup c WHERE c.created_by = u.id_user)
            LIMIT 1
            """;
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, email);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                Organisateur org = new Organisateur();
                org.setIdUser(rs.getInt("id_user"));
                org.setEmail(rs.getString("email"));
                org.setUsername(rs.getString("username"));
                org.setPasswordHash(rs.getString("password_hash"));
                return org;
            }
        } catch (SQLException e) {
            System.err.println("[AuthRepository] " + e.getMessage());
        }
        return null;
    }
}
