package vfoot.model;

public class Standing {
    private int    idUser;
    private String username;
    private String groupeNom;
    private int    played;
    private int    wins;
    private int    draws;
    private int    losses;
    private int    goalsFor;
    private int    goalsAgainst;
    private int    points;

    public int    getIdUser()       { return idUser; }
    public String getUsername()     { return username; }
    public String getGroupeNom()    { return groupeNom; }
    public int    getPlayed()       { return played; }
    public int    getWins()         { return wins; }
    public int    getDraws()        { return draws; }
    public int    getLosses()       { return losses; }
    public int    getGoalsFor()     { return goalsFor; }
    public int    getGoalsAgainst() { return goalsAgainst; }
    public int    getPoints()       { return points; }

    public void setIdUser(int v)         { this.idUser = v; }
    public void setUsername(String v)    { this.username = v; }
    public void setGroupeNom(String v)   { this.groupeNom = v; }
    public void setPlayed(int v)         { this.played = v; }
    public void setWins(int v)           { this.wins = v; }
    public void setDraws(int v)          { this.draws = v; }
    public void setLosses(int v)         { this.losses = v; }
    public void setGoalsFor(int v)       { this.goalsFor = v; }
    public void setGoalsAgainst(int v)   { this.goalsAgainst = v; }
    public void setPoints(int v)         { this.points = v; }
}
