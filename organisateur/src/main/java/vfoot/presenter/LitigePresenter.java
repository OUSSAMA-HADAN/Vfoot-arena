package vfoot.presenter;

import vfoot.model.Cup;
import vfoot.model.Match;
import vfoot.repository.CupRepository;
import vfoot.repository.MatchRepository;
import vfoot.view.ConsoleView;

import java.util.List;

public class LitigePresenter {

    //juste des variable de reference qui pointra vers un objet plus tards
    private final CupRepository   cupRepo;
    private final MatchRepository matchRepo;
    private final ConsoleView     view;

    public LitigePresenter(CupRepository cupRepo, MatchRepository matchRepo, ConsoleView view) {
        this.cupRepo   = cupRepo;
        this.matchRepo = matchRepo;
        this.view      = view;
    }

    public void gererLitiges(int orgId) {
        List<Cup> cups = cupRepo.findByOrganizer(orgId);
        view.showCups(cups);
        if (cups.isEmpty()) return;

        int idCup = view.askInt("ID de la cup : ");

        List<Match> litiges = matchRepo.findDisputedByCup(idCup);
        view.showDisputedMatches(litiges);
        if (litiges.isEmpty()) return;

        int idMatch = view.askInt("ID du match à résoudre (0 pour annuler) : ");
        if (idMatch == 0) return;

        Match match = litiges.stream()
                .filter(m -> m.getIdMatch() == idMatch)
                .findFirst()
                .orElse(null);

        if (match == null) {
            view.showError("Match introuvable dans la liste des litiges.");
            return;
        }

        System.out.printf("%n  Résolution : %s vs %s%n", match.getHomeName(), match.getAwayName());
        int homeScore = view.askInt("  Score " + match.getHomeName() + " : ");
        int awayScore = view.askInt("  Score " + match.getAwayName() + " : ");

        matchRepo.resolveDispute(match, homeScore, awayScore, orgId);
        view.showMessage("Litige résolu — résultat officiel : " + homeScore + " - " + awayScore);
    }
}
