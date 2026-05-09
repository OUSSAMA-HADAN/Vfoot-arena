package vfoot.view.console;

import vfoot.model.Qualifier;
import vfoot.view.IKnockoutView;

import java.util.List;
import java.util.Map;

public class ConsoleKnockoutView implements IKnockoutView {

    @Override
    public void showQualifiers(Map<String, List<Qualifier>> byGroup, int qualCount) {
        System.out.println("\n══════ QUALIFIÉS ══════");
        byGroup.forEach((group, players) -> {
            System.out.println("  Groupe " + group + " :");
            for (int i = 0; i < players.size(); i++) {
                Qualifier q = players.get(i);
                String mark = i < qualCount ? "  ✓" : "   ";
                System.out.printf("%s  %s%n", mark, q);
            }
        });
        System.out.println();
    }

    @Override
    public void showBracketGenerated(String round, int matchCount) {
        System.out.printf("%n✔ Bracket généré — Round : %s  (%d matchs créés)%n%n", round, matchCount);
    }

    @Override
    public void showWinners(String round, List<String> winnerNames) {
        System.out.printf("%n══════ VAINQUEURS %s ══════%n", round);
        winnerNames.forEach(name -> System.out.println("  ► " + name));
        System.out.println();
    }

    @Override
    public void showRoundAdvanced(String newRound) {
        System.out.printf("%n✔ Matchs du round %s créés avec succès.%n%n", newRound);
    }

    @Override
    public void showCupFinished(String winnerName) {
        System.out.println("\n╔══════════════════════════════╗");
        System.out.printf( "║  🏆 CHAMPION : %-14s║%n", winnerName);
        System.out.println("║  Cup clôturée avec succès.   ║");
        System.out.println("╚══════════════════════════════╝\n");
    }

    @Override
    public void showError(String message) {
        System.out.println("  [ERREUR] " + message);
    }

    @Override
    public void showInfo(String message) {
        System.out.println("  " + message);
    }
}
