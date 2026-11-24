package vfoot.presenter;

import vfoot.model.Cup;
import vfoot.model.Standing;
import vfoot.repository.BracketRepository;
import vfoot.repository.CupRepository;
import vfoot.view.ConsoleView;

import java.time.LocalDateTime;
import java.util.List;

public class EliminatoirePresenter {

    private final CupRepository     cupRepo;
    private final BracketRepository bracketRepo;
    private final ConsoleView       view;

    public EliminatoirePresenter(CupRepository cupRepo, BracketRepository bracketRepo, ConsoleView view) {
        this.cupRepo     = cupRepo;
        this.bracketRepo = bracketRepo;
        this.view        = view;
    }

    public void lancerEliminatoire(int orgId) {
        List<Cup> cups = cupRepo.findByOrganizer(orgId);
        view.showCups(cups);
        if (cups.isEmpty()) return;

        int idCup = view.askInt("ID de la cup : ");

        if (!cupRepo.areGroupsFinished(idCup)) {
            view.showError("Tous les matchs de groupe ne sont pas encore confirmés.");
            return;
        }

        String currentRound = bracketRepo.getCurrentRound(idCup);

        // Aucun match knockout → on génère le premier tour
        if (currentRound == null) {
            List<Standing> standings = cupRepo.getStandings(idCup);
            view.showStandings(standings);
            LocalDateTime deadline = view.askDeadline();
            bracketRepo.generateFirstRound(idCup, standings, deadline);
            view.showMessage("Phase éliminatoire lancée — matchs générés !");
            return;
        }

        // La finale est déjà jouée → cup terminée
        if (currentRound.equals("F") && bracketRepo.isRoundComplete(idCup, "F")) {
            view.showMessage("La cup est terminée — le champion a été désigné !");
            return;
        }

        // Le tour en cours n'est pas encore fini
        if (!bracketRepo.isRoundComplete(idCup, currentRound)) {
            view.showError("Le tour " + currentRound + " est encore en cours — attendez que tous les matchs soient confirmés.");
            return;
        }

        // Tour terminé → on génère le suivant
        String nextRound = bracketRepo.getNextRound(currentRound);
        view.showMessage("Tour " + currentRound + " terminé. Génération du tour : " + nextRound);

        LocalDateTime deadline = view.askDeadline();
        boolean ok = bracketRepo.generateNextRound(idCup, currentRound, deadline);

        if (ok) view.showMessage("Tour " + nextRound + " généré avec succès !");
        else    view.showError("Égalité en agrégat — un barrage (leg 3) est nécessaire avant de continuer.");
    }
}
