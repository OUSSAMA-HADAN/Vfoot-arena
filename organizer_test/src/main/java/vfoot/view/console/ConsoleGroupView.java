package vfoot.view.console;

import vfoot.view.IGroupView;

import java.util.List;

public class ConsoleGroupView implements IGroupView {

    @Override
    public void showParticipants(List<String[]> participants) {
        System.out.printf("%n══════ PARTICIPANTS (%d) ══════%n", participants.size());
        for (int i = 0; i < participants.size(); i++) {
            System.out.printf("  %2d. %s%n", i + 1, participants.get(i)[1]);
        }
        System.out.println();
    }

    @Override
    public void showGroupsGenerated(int numGroups, int numMatches) {
        System.out.printf("%n✔ %d groupe(s) générés — %d matchs créés.%n%n", numGroups, numMatches);
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
