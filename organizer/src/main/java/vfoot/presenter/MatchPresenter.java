package vfoot.presenter;

import vfoot.model.Match;
import vfoot.repository.MatchRepository;
import vfoot.view.IMatchView;

public class MatchPresenter {

    private final IMatchView      view;
    private final MatchRepository repo;

    public MatchPresenter(IMatchView view, MatchRepository repo) {
        this.view = view;
        this.repo = repo;
    }

    public void listAll() {
        try {
            view.showMatches(repo.findAll());
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    public void listDisputed() {
        try {
            view.showDisputedMatches(repo.findDisputed());
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }

    /**
     * L'organisateur impose le score final d'un match en litige.
     */
    public void resolveDispute(int matchId, int homeScore, int awayScore) {
        if (homeScore < 0 || awayScore < 0) {
            view.showError("Les scores doivent être >= 0.");
            return;
        }
        try {
            Match match = repo.findById(matchId);
            if (match == null) {
                view.showError("Match #" + matchId + " introuvable.");
                return;
            }
            if (!"disputed".equals(match.getStatut())) {
                view.showError("Match #" + matchId + " n'est pas en litige (statut=" + match.getStatut() + ").");
                return;
            }
            repo.resolveDispute(matchId, homeScore, awayScore);
            view.showResolved(matchId, homeScore, awayScore);
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }
}
