package vfoot.view;

import java.util.List;
import java.util.Map;

public interface IGroupView {
    void showParticipants(List<String[]> participants);
    void showGroupsGenerated(int numGroups, int numMatches);
    void showError(String message);
    void showInfo(String message);
}
