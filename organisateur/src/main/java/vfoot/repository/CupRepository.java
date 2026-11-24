package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Cup;
import vfoot.model.Standing;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

public class CupRepository {

    private final Connection conn = Database.get();

    public List<Cup> findByOrganizer(int orgId) {
        List<Cup> list = new ArrayList<>();
        String sql = "SELECT id_cup, titre, statut FROM cup WHERE created_by = ? ORDER BY id_cup";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, orgId);
            ResultSet rs = stmt.executeQuery();
            //mapping SQL -> Objet
            while (rs.next()) {
                Cup c = new Cup();
                c.setIdCup(rs.getInt("id_cup"));
                c.setTitre(rs.getString("titre"));
                c.setStatut(rs.getString("statut"));
                // on ajout l'objet a la list des objet a retourner a la fin
                list.add(c);
            }
        } catch (SQLException e) {
            System.err.println("[CupRepository] findByOrganizer : " + e.getMessage());
        }
        return list;// form [cup,cup,cup...]
    }

    // Vérifie que tous les matchs de groupe sont confirmés avant de lancer l'élim.
    public boolean areGroupsFinished(int idCup) {
        String sql = """
            SELECT COUNT(*) FROM matche
            WHERE id_cup = ? AND round = 'group' AND statut != 'confirmed'
            """;
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idCup);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) return rs.getInt(1) == 0;
        } catch (SQLException e) {
            System.err.println("[CupRepository] areGroupsFinished : " + e.getMessage());
        }
        return false;
    }

    // Classement de tous les groupes d'une cup, trié par points puis diff. de buts.
    public List<Standing> getStandings(int idCup) {
        List<Standing> list = new ArrayList<>();
        String sql = """
            SELECT u.id_user, u.username, g.nom AS groupe_nom,
                   e.played, e.wins, e.draws, e.losses,
                   e.goals_for, e.goals_against, e.points
            FROM est_classe_dans e
            JOIN users  u ON e.id_user   = u.id_user
            JOIN groupe g ON e.id_groupe = g.id_groupe
            WHERE e.id_cup = ? AND e.phase = 'group'
            ORDER BY g.id_groupe,
                     e.points DESC,
                     (e.goals_for - e.goals_against) DESC
            """;
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idCup);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Standing s = new Standing();
                s.setIdUser(rs.getInt("id_user"));
                s.setUsername(rs.getString("username"));
                s.setGroupeNom(rs.getString("groupe_nom"));
                s.setPlayed(rs.getInt("played"));
                s.setWins(rs.getInt("wins"));
                s.setDraws(rs.getInt("draws"));
                s.setLosses(rs.getInt("losses"));
                s.setGoalsFor(rs.getInt("goals_for"));
                s.setGoalsAgainst(rs.getInt("goals_against"));
                s.setPoints(rs.getInt("points"));
                list.add(s);
            }
        } catch (SQLException e) {
            System.err.println("[CupRepository] getStandings : " + e.getMessage());
        }
        return list;
    }

    // Crée une cup et retourne son id_cup, ou -1 en cas d'erreur.
    public int create(String titre, String visibilite, String joinCode,
                      int minPlayers, int maxPlayers, int qualification, int createdBy) {
        String sql = """
            INSERT INTO cup (titre, visibilite, join_code, min_players, max_players, qualification, statut, created_by)
            VALUES (?, ?, ?, ?, ?, ?, 'open', ?)
            """;
        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, titre);
            stmt.setString(2, visibilite);
            if (joinCode != null) stmt.setString(3, joinCode);
            else                  stmt.setNull(3, Types.VARCHAR);
            stmt.setInt(4, minPlayers);
            stmt.setInt(5, maxPlayers);
            stmt.setInt(6, qualification);
            stmt.setInt(7, createdBy);
            stmt.executeUpdate();
            ResultSet keys = stmt.getGeneratedKeys();
            if (keys.next()) return keys.getInt(1);
        } catch (SQLException e) {
            System.err.println("[CupRepository] create : " + e.getMessage());
        }
        return -1;
    }

    // Inscrit l'organisateur dans la table participe.
    public void addOrganizer(int idCup, int idUser) {
        String sql = "INSERT INTO participe (id_user, id_cup, role) VALUES (?, ?, 'organizer')";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idUser);
            stmt.setInt(2, idCup);
            stmt.executeUpdate();
        } catch (SQLException e) {
            System.err.println("[CupRepository] addOrganizer : " + e.getMessage());
        }
    }
}
