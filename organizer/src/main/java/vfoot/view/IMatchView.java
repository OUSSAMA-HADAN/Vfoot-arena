package vfoot.view;

import vfoot.model.Match;
import java.util.List;

public interface IMatchView {
    void showMatches(List<Match> matches);
    void showDisputedMatches(List<Match> matches);

    /** Appelée après résolution d'un litige. */
    void showResolved(int matchId, int homeScore, int awayScore);

    void showError(String message);
}
