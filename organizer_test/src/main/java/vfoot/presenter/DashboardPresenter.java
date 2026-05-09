package vfoot.presenter;

import vfoot.repository.CupRepository;
import vfoot.repository.MatchRepository;
import vfoot.view.IDashboardView;

/**
 * Collecte les stats depuis les repositories
 * et demande à la View de les afficher.
 *
 * Le Presenter ne sait pas que la View est une console —
 * il appelle uniquement l'interface IDashboardView.
 */
public class DashboardPresenter {

    private final IDashboardView view;
    private final CupRepository  cupRepo;
    private final MatchRepository matchRepo;

    public DashboardPresenter(IDashboardView view, CupRepository cupRepo, MatchRepository matchRepo) {
        this.view      = view;
        this.cupRepo   = cupRepo;
        this.matchRepo = matchRepo;
    }

    public void loadStats() {
        try {
            int cupsStarted    = cupRepo.countByStatut("started");
            int cupsFinished   = cupRepo.countByStatut("finished");
            int confirmed      = matchRepo.countByStatut("confirmed");
            int disputed       = matchRepo.countByStatut("disputed");

            view.showStats(cupsStarted, cupsFinished, confirmed, disputed);
        } catch (Exception e) {
            view.showError(e.getMessage());
        }
    }
}
