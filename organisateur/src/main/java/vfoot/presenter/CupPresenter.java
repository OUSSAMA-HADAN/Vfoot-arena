package vfoot.presenter;

import vfoot.repository.CupRepository;
import vfoot.view.ConsoleView;

public class CupPresenter {

    private final CupRepository cupRepo;
    private final ConsoleView   view;

    public CupPresenter(CupRepository cupRepo, ConsoleView view) {
        this.cupRepo = cupRepo;
        this.view    = view;
    }

    public void listerCups(int orgId) {
        view.showCups(cupRepo.findByOrganizer(orgId));
    }

    public void creerCup(int orgId) {
        System.out.println("\n── Nouvelle cup ──────────────────");

        String titre      = view.askString("  Titre : ");
        String visibilite = view.askVisibilite();
        String joinCode   = null;
        if (visibilite.equals("private")) {
            joinCode = view.askString("  Code d'invitation : ");
        }
        int minPlayers    = view.askIntInRange("  Min joueurs (8-64) : ", 8, 64);
        int maxPlayers    = view.askIntInRange("  Max joueurs (" + minPlayers + "-64) : ", minPlayers, 64);
        int qualification = view.askIntInRange("  Places qualif. par groupe (1-2) : ", 1, 2);

        int idCup = cupRepo.create(titre, visibilite, joinCode, minPlayers, maxPlayers, qualification, orgId);
        if (idCup > 0) {
            cupRepo.addOrganizer(idCup, orgId);
            view.showMessage("Cup \"" + titre + "\" créée ! (ID=" + idCup + ", statut=open)");
        } else {
            view.showError("Échec de la création.");
        }
    }
}
