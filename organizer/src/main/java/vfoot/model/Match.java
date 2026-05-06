package vfoot.model;

public class Match {
    private int    id;
    private String round;       // group | R16 | QF | SF | F
    private String leg;         // 1 | 2 | 3 | single
    private String statut;      // pending | submitted | confirmed | disputed
    private String homeName;
    private String awayName;
    private Integer homeScore;  // null si pas encore confirmé
    private Integer awayScore;
    private Integer subHomeH;   // soumission du joueur domicile
    private Integer subAwayH;
    private Integer subHomeA;   // soumission du joueur extérieur
    private Integer subAwayA;
    private String cupTitre;

    public Match(int id, String round, String leg, String statut,
                 String homeName, String awayName,
                 Integer homeScore, Integer awayScore,
                 Integer subHomeH, Integer subAwayH,
                 Integer subHomeA, Integer subAwayA,
                 String cupTitre) {
        this.id        = id;
        this.round     = round;
        this.leg       = leg;
        this.statut    = statut;
        this.homeName  = homeName;
        this.awayName  = awayName;
        this.homeScore = homeScore;
        this.awayScore = awayScore;
        this.subHomeH  = subHomeH;
        this.subAwayH  = subAwayH;
        this.subHomeA  = subHomeA;
        this.subAwayA  = subAwayA;
        this.cupTitre  = cupTitre;
    }

    public int     getId()       { return id; }
    public String  getRound()    { return round; }
    public String  getLeg()      { return leg; }
    public String  getStatut()   { return statut; }
    public String  getHomeName() { return homeName; }
    public String  getAwayName() { return awayName; }
    public Integer getHomeScore(){ return homeScore; }
    public Integer getAwayScore(){ return awayScore; }
    public Integer getSubHomeH() { return subHomeH; }
    public Integer getSubAwayH() { return subAwayH; }
    public Integer getSubHomeA() { return subHomeA; }
    public Integer getSubAwayA() { return subAwayA; }
    public String  getCupTitre() { return cupTitre; }

    @Override
    public String toString() {
        String score = (homeScore != null)
                ? homeScore + " - " + awayScore
                : "? - ?";
        return String.format("[%d] %-8s leg=%-6s  %-12s vs %-12s  score=%-6s  statut=%s  cup=%s",
                id, round, leg, homeName, awayName, score, statut, cupTitre);
    }
}
