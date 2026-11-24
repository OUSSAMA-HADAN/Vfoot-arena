package vfoot.model;

public class Match {
    private int     idMatch;
    private int     homeUserId;
    private int     awayUserId;
    private String  homeName;
    private String  awayName;
    private String  statut;
    private String  round;
    private int     idCup;
    private Integer idGroupe;

    // Soumissions des deux joueurs (pour affichage litige)
    private Integer homeSubHome;
    private Integer homeSubAway;
    private Integer awaySubHome;
    private Integer awaySubAway;

    public int     getIdMatch()     { return idMatch; }
    public int     getHomeUserId()  { return homeUserId; }
    public int     getAwayUserId()  { return awayUserId; }
    public String  getHomeName()    { return homeName; }
    public String  getAwayName()    { return awayName; }
    public String  getStatut()      { return statut; }
    public String  getRound()       { return round; }
    public int     getIdCup()        { return idCup; }
    public Integer getIdGroupe()    { return idGroupe; }
    public Integer getHomeSubHome() { return homeSubHome; }
    public Integer getHomeSubAway() { return homeSubAway; }
    public Integer getAwaySubHome() { return awaySubHome; }
    public Integer getAwaySubAway() { return awaySubAway; }

    public void setIdMatch(int v)         { this.idMatch = v; }
    public void setHomeUserId(int v)      { this.homeUserId = v; }
    public void setAwayUserId(int v)      { this.awayUserId = v; }
    public void setHomeName(String v)     { this.homeName = v; }
    public void setAwayName(String v)     { this.awayName = v; }
    public void setStatut(String v)       { this.statut = v; }
    public void setRound(String v)        { this.round = v; }
    public void setIdCup(int v)           { this.idCup = v; }
    public void setIdGroupe(Integer v)    { this.idGroupe = v; }
    public void setHomeSubHome(Integer v) { this.homeSubHome = v; }
    public void setHomeSubAway(Integer v) { this.homeSubAway = v; }
    public void setAwaySubHome(Integer v) { this.awaySubHome = v; }
    public void setAwaySubAway(Integer v) { this.awaySubAway = v; }
}
