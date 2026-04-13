package vfoot.view.console;

import vfoot.model.Match;
import vfoot.view.IMatchView;

import java.util.List;

public class ConsoleMatchView implements IMatchView {

    @Override
    public void showMatches(List<Match> matches) {
        System.out.println();
        System.out.println("── Matchs (" + matches.size() + ") ──────────────────────────────────────────");
        if (matches.isEmpty()) {
            System.out.println("  Aucun match.");
        } else {
            matches.forEach(m -> System.out.println("  " + m));
        }
        System.out.println();
    }

    @Override
    public void showDisputedMatches(List<Match> matches) {
        System.out.println();
        System.out.println("── Matchs en LITIGE (" + matches.size() + ") ────────────────────────────────");
        if (matches.isEmpty()) {
            System.out.println("  Aucun litige. Tout est en ordre.");
        } else {
            for (Match m : matches) {
                System.out.println("  " + m);
                System.out.printf("    > %s a soumis : %s-%s%n",
                        m.getHomeName(), m.getSubHomeH(), m.getSubAwayH());
                System.out.printf("    > %s a soumis : %s-%s%n",
                        m.getAwayName(), m.getSubHomeA(), m.getSubAwayA());
            }
        }
        System.out.println();
    }

    @Override
    public void showResolved(int matchId, int homeScore, int awayScore) {
        System.out.println("[OK] Match #" + matchId + " résolu → score final : " + homeScore + " - " + awayScore);
    }

    @Override
    public void showError(String message) {
        System.out.println("[ERREUR] " + message);
    }
}
