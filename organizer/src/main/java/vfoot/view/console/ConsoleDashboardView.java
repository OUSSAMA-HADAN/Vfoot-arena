package vfoot.view.console;

import vfoot.view.IDashboardView;

public class ConsoleDashboardView implements IDashboardView {

    @Override
    public void showStats(int cupsStarted, int cupsFinished, int matchesConfirmed, int matchesDisputed) {
        System.out.println();
        System.out.println("╔══════════════════════════════════════╗");
        System.out.println("║       VFoot Arena — Dashboard        ║");
        System.out.println("╠══════════════════════════════════════╣");
        System.out.printf( "║  Cups en cours      : %-15d║%n", cupsStarted);
        System.out.printf( "║  Cups terminées     : %-15d║%n", cupsFinished);
        System.out.printf( "║  Matchs confirmés   : %-15d║%n", matchesConfirmed);
        System.out.printf( "║  Matchs en litige   : %-15d║%n", matchesDisputed);
        System.out.println("╚══════════════════════════════════════╝");
        System.out.println();
    }

    @Override
    public void showError(String message) {
        System.out.println("[ERREUR] " + message);
    }
}
