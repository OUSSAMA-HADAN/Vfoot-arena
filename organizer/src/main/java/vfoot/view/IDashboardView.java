package vfoot.view;

public interface IDashboardView {
    /** Affiche les statistiques globales de la plateforme. */
    void showStats(int cupsStarted, int cupsFinished, int matchesConfirmed, int matchesDisputed);

    void showError(String message);
}
