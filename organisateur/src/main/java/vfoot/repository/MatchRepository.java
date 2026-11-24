package vfoot.repository;

import vfoot.config.Database;
import vfoot.model.Match;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class MatchRepository {

    private final Connection conn = Database.get();

    // Tous les matchs en litige pour une cup donnée.
    public List<Match> findDisputedByCup(int idCup) {
        List<Match> list = new ArrayList<>();
        String sql = """
            SELECT m.id_match, m.home_user_id, m.away_user_id,
                   h.username AS home_name, a.username AS away_name,
                   m.round, m.id_groupe, m.id_cup,
                   sh.home_score AS h_home, sh.away_score AS h_away,
                   sa.home_score AS a_home, sa.away_score AS a_away
            FROM matche m
            JOIN users h ON m.home_user_id = h.id_user
            JOIN users a ON m.away_user_id = a.id_user
            LEFT JOIN submission sh ON sh.id_match = m.id_match AND sh.id_user = m.home_user_id
            LEFT JOIN submission sa ON sa.id_match = m.id_match AND sa.id_user = m.away_user_id
            WHERE m.id_cup = ? AND m.statut = 'disputed'
            ORDER BY m.id_match
            """;
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, idCup);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Match m = new Match();
                m.setIdMatch(rs.getInt("id_match"));
                m.setHomeUserId(rs.getInt("home_user_id"));
                m.setAwayUserId(rs.getInt("away_user_id"));
                m.setHomeName(rs.getString("home_name"));
                m.setAwayName(rs.getString("away_name"));
                m.setRound(rs.getString("round"));
                m.setIdCup(rs.getInt("id_cup"));
                int idGroupe = rs.getInt("id_groupe");
                m.setIdGroupe(rs.wasNull() ? null : idGroupe);
                m.setHomeSubHome(rs.getInt("h_home"));
                m.setHomeSubAway(rs.getInt("h_away"));
                m.setAwaySubHome(rs.getInt("a_home"));
                m.setAwaySubAway(rs.getInt("a_away"));
                list.add(m);
            }
        } catch (SQLException e) {
            System.err.println("[MatchRepository] findDisputedByCup : " + e.getMessage());
        }
        return list;
    }

    // Résout un litige : insert résultat officiel + confirme le match + met à jour classement si groupe.
    public void resolveDispute(Match match, int homeScore, int awayScore, int confirmedBy) {
        try {
            conn.setAutoCommit(false);

            PreparedStatement ins = conn.prepareStatement(
                "INSERT INTO resultat (home_score, away_score, id_match, confirmed_by) VALUES (?,?,?,?)"
            );
            ins.setInt(1, homeScore);
            ins.setInt(2, awayScore);
            ins.setInt(3, match.getIdMatch());
            ins.setInt(4, confirmedBy);
            ins.executeUpdate();

            PreparedStatement upd = conn.prepareStatement(
                "UPDATE matche SET statut = 'confirmed' WHERE id_match = ?"
            );
            upd.setInt(1, match.getIdMatch());
            upd.executeUpdate();

            // Si c'est un match de groupe, on met à jour le classement.
            if (match.getIdGroupe() != null) {
                updateStandings(match.getHomeUserId(), match.getAwayUserId(),
                                homeScore, awayScore, match.getIdCup());
            }

            conn.commit();
        } catch (SQLException e) {
            try { conn.rollback(); } catch (SQLException ignored) {}
            System.err.println("[MatchRepository] resolveDispute : " + e.getMessage());
        } finally {
            try { conn.setAutoCommit(true); } catch (SQLException ignored) {}
        }
    }

    private void updateStandings(int homeId, int awayId, int hs, int as, int idCup) throws SQLException {
        String sql = """
            UPDATE est_classe_dans
            SET played        = played + 1,
                wins          = wins   + ?,
                draws         = draws  + ?,
                losses        = losses + ?,
                goals_for     = goals_for     + ?,
                goals_against = goals_against + ?,
                points        = points + ?
            WHERE id_user = ? AND id_cup = ? AND phase = 'group'
            """;
        PreparedStatement stmt = conn.prepareStatement(sql);

        if (hs > as) {
            stmt.setInt(1,1);stmt.setInt(2,0);stmt.setInt(3,0);stmt.setInt(4,hs);stmt.setInt(5,as);stmt.setInt(6,3);stmt.setInt(7,homeId);stmt.setInt(8,idCup);stmt.executeUpdate();
            stmt.setInt(1,0);stmt.setInt(2,0);stmt.setInt(3,1);stmt.setInt(4,as);stmt.setInt(5,hs);stmt.setInt(6,0);stmt.setInt(7,awayId);stmt.setInt(8,idCup);stmt.executeUpdate();
        } else if (as > hs) {
            stmt.setInt(1,0);stmt.setInt(2,0);stmt.setInt(3,1);stmt.setInt(4,hs);stmt.setInt(5,as);stmt.setInt(6,0);stmt.setInt(7,homeId);stmt.setInt(8,idCup);stmt.executeUpdate();
            stmt.setInt(1,1);stmt.setInt(2,0);stmt.setInt(3,0);stmt.setInt(4,as);stmt.setInt(5,hs);stmt.setInt(6,3);stmt.setInt(7,awayId);stmt.setInt(8,idCup);stmt.executeUpdate();
        } else {
            stmt.setInt(1,0);stmt.setInt(2,1);stmt.setInt(3,0);stmt.setInt(4,hs);stmt.setInt(5,as);stmt.setInt(6,1);stmt.setInt(7,homeId);stmt.setInt(8,idCup);stmt.executeUpdate();
            stmt.setInt(1,0);stmt.setInt(2,1);stmt.setInt(3,0);stmt.setInt(4,as);stmt.setInt(5,hs);stmt.setInt(6,1);stmt.setInt(7,awayId);stmt.setInt(8,idCup);stmt.executeUpdate();
        }
    }
}
