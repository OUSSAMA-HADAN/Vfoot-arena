package vfoot.model;

public class Cup {
    private int id;
    private String titre;
    private String statut;      // pending | started | finished
    private String visibilite;  // public  | private
    private int minPlayers;
    private int maxPlayers;

    public Cup(int id, String titre, String statut, String visibilite, int minPlayers, int maxPlayers) {
        this.id          = id;
        this.titre       = titre;
        this.statut      = statut;
        this.visibilite  = visibilite;
        this.minPlayers  = minPlayers;
        this.maxPlayers  = maxPlayers;
    }

    public int    getId()         { return id; }
    public String getTitre()      { return titre; }
    public String getStatut()     { return statut; }
    public String getVisibilite() { return visibilite; }
    public int    getMinPlayers() { return minPlayers; }
    public int    getMaxPlayers() { return maxPlayers; }

    @Override
    public String toString() {
        return String.format("[%d] %-25s  statut=%-10s  visibilite=%s  joueurs=%d/%d",
                id, titre, statut, visibilite, minPlayers, maxPlayers);
    }
}
