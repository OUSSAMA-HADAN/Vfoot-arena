package vfoot;


import vfoot.model.Organisateur;
import vfoot.presenter.*;
import vfoot.repository.*;
import vfoot.view.ConsoleView;

import java.util.Scanner;

public class Main {

    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        ConsoleView view = new ConsoleView(scanner);

        // --- Authentification ------------------------
        AuthPresenter authPresenter = new AuthPresenter(new AuthRepository(), view);
        Organisateur  org = authPresenter.login();

        // --- Presenters ---------------------------
        CupRepository     cupRepo     = new CupRepository();
        BracketRepository bracketRepo = new BracketRepository();
        MatchRepository   matchRepo   = new MatchRepository();

        CupPresenter          cupPresenter          = new CupPresenter(cupRepo, view);
        EliminatoirePresenter eliminatoirePresenter = new EliminatoirePresenter(cupRepo, bracketRepo, view);
        LitigePresenter       litigePresenter       = new LitigePresenter(cupRepo, matchRepo, view);

        // --- Menu principal -------------------------
        boolean running = true;
        while (running) {
            view.showMenu(org.getUsername());
            int choix = view.askInt("Votre choix : ");
            switch (choix) {
                case 1 -> cupPresenter.listerCups(org.getIdUser());
                case 2 -> cupPresenter.creerCup(org.getIdUser());
                case 3 -> eliminatoirePresenter.lancerEliminatoire(org.getIdUser());
                case 4 -> litigePresenter.gererLitiges(org.getIdUser());
                case 5 -> running = false;
                default -> view.showError("Choix invalide, entrez 1 à 5.");
            }
        }

        scanner.close();
        System.out.println("\n  Au revoir !");
    }
}
