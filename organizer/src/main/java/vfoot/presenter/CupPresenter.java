package vfoot.presenter;

import vfoot.repository.CupRepository;
import vfoot.view.ICupView;

public class CupPresenter {

    private final ICupView      view;
    private final CupRepository repo;

    public CupPresenter(ICupView view, CupRepository repo) {
        this.view = view;
        this.repo = repo;
    }

    public void listCups() {
        try {
            view.showCups(repo.findAll());
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    /**
     * @param createdBy  id de l'utilisateur organisateur connecté
     */
    public void createCup(String titre, int minPlayers, int maxPlayers, int createdBy) {
        if (titre == null || titre.isBlank()) {
            view.showError("Le titre ne peut pas être vide.");
            return;
        }
        if (minPlayers < 2 || maxPlayers < minPlayers) {
            view.showError("Nombre de joueurs invalide (min >= 2, max >= min).");
            return;
        }
        try {
            int id = repo.create(titre, minPlayers, maxPlayers, createdBy);
            view.showCupCreated(id, titre);
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    public void changeStatut(int cupId, String newStatut) {
        try {
            repo.updateStatut(cupId, newStatut);
            view.showCupUpdated(cupId, newStatut);
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }
}
