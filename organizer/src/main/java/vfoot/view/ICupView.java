package vfoot.view;

import vfoot.model.Cup;
import java.util.List;

public interface ICupView {
    void showCups(List<Cup> cups);
    void showCupCreated(int id, String titre);
    void showCupUpdated(int id, String newStatut);
    void showError(String message);
}
