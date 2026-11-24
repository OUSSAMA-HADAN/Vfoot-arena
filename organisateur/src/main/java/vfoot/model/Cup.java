package vfoot.model;

public class Cup {
    private int    idCup;
    private String titre;
    private String statut;

    public int    getIdCup()   { return idCup; }
    public String getTitre()   { return titre; }
    public String getStatut()  { return statut; }

    public void setIdCup(int idCup)      { this.idCup = idCup; }
    public void setTitre(String titre)   { this.titre = titre; }
    public void setStatut(String statut) { this.statut = statut; }

    @Override
    public String toString() {
        return String.format("[%d] %s (%s)", idCup, titre, statut);
    }
}
