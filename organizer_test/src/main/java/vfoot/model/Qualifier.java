package vfoot.model;

/** Représente un joueur qualifié depuis la phase de groupes. */
public class Qualifier {

    private final int    userId;
    private final String username;
    private final String groupName;
    private final int    points;
    private final int    goalDiff;
    private final int    rank;   // 1 = 1er du groupe, 2 = 2ème, ...

    public Qualifier(int userId, String username, String groupName,
                     int points, int goalDiff, int rank) {
        this.userId    = userId;
        this.username  = username;
        this.groupName = groupName;
        this.points    = points;
        this.goalDiff  = goalDiff;
        this.rank      = rank;
    }

    public int    getUserId()   { return userId; }
    public String getUsername() { return username; }
    public String getGroupName(){ return groupName; }
    public int    getPoints()   { return points; }
    public int    getGoalDiff() { return goalDiff; }
    public int    getRank()     { return rank; }

    @Override
    public String toString() {
        return String.format("Groupe %s #%d  %-15s  pts=%d  db=%+d",
                groupName, rank, username, points, goalDiff);
    }
}
