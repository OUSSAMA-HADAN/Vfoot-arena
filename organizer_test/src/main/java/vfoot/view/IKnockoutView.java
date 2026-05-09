package vfoot.view;

import vfoot.model.Qualifier;
import java.util.List;
import java.util.Map;

public interface IKnockoutView {
    void showQualifiers(Map<String, List<Qualifier>> byGroup, int qualCount);
    void showBracketGenerated(String round, int matchCount);
    void showWinners(String round, List<String> winnerNames);
    void showRoundAdvanced(String newRound);
    void showCupFinished(String winnerName);
    void showError(String message);
    void showInfo(String message);
}
