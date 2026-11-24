package vfoot.presenter;

import org.mindrot.jbcrypt.BCrypt;
import vfoot.model.Organisateur;
import vfoot.repository.AuthRepository;
import vfoot.view.ConsoleView;

public class AuthPresenter {

    private final AuthRepository repo;
    private final ConsoleView    view;

    public AuthPresenter(AuthRepository repo, ConsoleView view) {
        this.repo = repo;
        this.view = view;
    }

    public Organisateur login() {
        System.out.println("\n  VFoot Arena — Espace Organisateur");
        System.out.println("  ──────────────────────────────────");

        while (true) {
            String email    = view.askEmail();
            String password = view.askPassword();

            Organisateur org = repo.findByEmail(email);
            if (org != null) {
                String hash = org.getPasswordHash().replace("$2y$", "$2a$");
                if (BCrypt.checkpw(password, hash)) {
                    view.showMessage("Bienvenue, " + org.getUsername() + " !");
                    return org;
                }
            }

            view.showError("Identifiants invalides. Réessayez.");
        }
    }
}
