package vfoot.repository;

import vfoot.config.Database;

import java.sql.*;
import java.util.*;

public class GroupRepository {

    private final Connection conn = Database.get();

    // ── Retourne les id_user des joueurs inscrits dans une cup ───────────────
    public List<int[]> getParticipants(int cupId) throws SQLException {
        String sql = """
                SELECT u.id_user, u.username
                FROM   participe p
                JOIN   users     u ON u.id_user = p.id_user
                WHERE  p.id_cup = ? AND p.role = 'player'
                ORDER  BY p.date_adhesion ASC
                """;
        List<int[]> result = new ArrayList<>();
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    result.add(new int[]{rs.getInt("id_user")});
                }
            }
        }
        return result;
    }

    // ── Retourne les noms des joueurs inscrits ────────────────────────────────
    public List<String[]> getParticipantsWithNames(int cupId) throws SQLException {
        String sql = """
                SELECT u.id_user, u.username
                FROM   participe p
                JOIN   users     u ON u.id_user = p.id_user
                WHERE  p.id_cup = ? AND p.role = 'player'
                ORDER  BY p.date_adhesion ASC
                """;
        List<String[]> result = new ArrayList<>();
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    result.add(new String[]{
                        String.valueOf(rs.getInt("id_user")),
                        rs.getString("username")
                    });
                }
            }
        }
        return result;
    }

    // ── Vérifie que des groupes n'existent pas déjà pour cette cup ───────────
    public boolean groupsAlreadyExist(int cupId) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(
                "SELECT COUNT(*) FROM groupe WHERE id_cup = ?")) {
            ps.setInt(1, cupId);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() && rs.getInt(1) > 0;
            }
        }
    }

    // ── Génère les groupes, assigne les joueurs, crée les matchs ─────────────
    // players : liste de id_user mélangés aléatoirement
    // numGroups : nombre de groupes voulus
    public void generate(int cupId, List<Integer> players, int numGroups) throws SQLException {
        conn.setAutoCommit(false);
        try {
            // 1. Créer les groupes (A, B, C, ...)
            List<Integer> groupIds = new ArrayList<>();
            for (int i = 0; i < numGroups; i++) {
                char letter = (char) ('A' + i);
                groupIds.add(createGroup(cupId, String.valueOf(letter)));
            }

            // 2. Distribuer les joueurs round-robin dans les groupes
            //    ex: 8 joueurs, 2 groupes → A:1,3,5,7  B:2,4,6,8
            Map<Integer, List<Integer>> groupPlayers = new LinkedHashMap<>();
            for (int gId : groupIds) groupPlayers.put(gId, new ArrayList<>());

            for (int i = 0; i < players.size(); i++) {
                int groupId = groupIds.get(i % numGroups);
                groupPlayers.get(groupId).add(players.get(i));
            }

            // 3. Insérer dans est_classe_dans + créer les matchs aller-retour
            for (Map.Entry<Integer, List<Integer>> entry : groupPlayers.entrySet()) {
                int groupId = entry.getKey();
                List<Integer> members = entry.getValue();

                for (int userId : members) {
                    insertStanding(groupId, userId);
                }

                createRoundRobinMatches(cupId, groupId, members);
            }

            conn.commit();
        } catch (SQLException e) {
            conn.rollback();
            throw e;
        } finally {
            conn.setAutoCommit(true);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Privé
    // ═══════════════════════════════════════════════════════════════════════════

    private int createGroup(int cupId, String nom) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(
                "INSERT INTO groupe (nom, id_cup) VALUES (?, ?)",
                Statement.RETURN_GENERATED_KEYS)) {
            ps.setString(1, nom);
            ps.setInt(2, cupId);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) return keys.getInt(1);
            }
        }
        throw new SQLException("Impossible de créer le groupe");
    }

    private void insertStanding(int groupId, int userId) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(
                "INSERT IGNORE INTO est_classe_dans (id_user, id_groupe) VALUES (?, ?)")) {
            ps.setInt(1, userId);
            ps.setInt(2, groupId);
            ps.executeUpdate();
        }
    }

    // Crée tous les matchs aller (leg 1) et retour (leg 2) pour un groupe.
    // Note : group leg 2 a first_leg_id = NULL (contrainte DB — matchs indépendants).
    private void createRoundRobinMatches(int cupId, int groupId, List<Integer> members) throws SQLException {
        String sql = """
                INSERT INTO matche (round, leg, statut, id_cup, id_groupe, home_user_id, away_user_id, first_leg_id)
                VALUES ('group', ?, 'pending', ?, ?, ?, ?, NULL)
                """;
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            for (int i = 0; i < members.size(); i++) {
                for (int j = i + 1; j < members.size(); j++) {
                    int a = members.get(i);
                    int b = members.get(j);

                    // Leg 1 : A à domicile
                    ps.setString(1, "1");
                    ps.setInt(2, cupId);
                    ps.setInt(3, groupId);
                    ps.setInt(4, a);
                    ps.setInt(5, b);
                    ps.executeUpdate();

                    // Leg 2 : B à domicile (first_leg_id = NULL pour les groupes)
                    ps.setString(1, "2");
                    ps.setInt(2, cupId);
                    ps.setInt(3, groupId);
                    ps.setInt(4, b);
                    ps.setInt(5, a);
                    ps.executeUpdate();
                }
            }
        }
    }
}
