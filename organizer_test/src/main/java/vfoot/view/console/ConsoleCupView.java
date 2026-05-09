package vfoot.view.console;

import vfoot.model.Cup;
import vfoot.view.ICupView;

import java.util.List;

public class ConsoleCupView implements ICupView {

    @Override
    public void showCups(List<Cup> cups) {
        System.out.println();
        System.out.println("── Cups (" + cups.size() + ") ──────────────────────────────────────────────");
        if (cups.isEmpty()) {
            System.out.println("  Aucune cup.");
        } else {
            cups.forEach(c -> System.out.println("  " + c));
        }
        System.out.println();
    }

    @Override
    public void showCupCreated(int id, String titre) {
        System.out.println("[OK] Cup créée — id=" + id + "  titre=" + titre);
    }

    @Override
    public void showCupUpdated(int id, String newStatut) {
        System.out.println("[OK] Cup #" + id + " → statut=" + newStatut);
    }

    @Override
    public void showError(String message) {
        System.out.println("[ERREUR] " + message);
    }
}
