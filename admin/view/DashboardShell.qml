import QtQuick
import QtQuick.Layouts

Rectangle {
    id: root
    anchors.fill: parent
    color: "#0a0e27"

    RowLayout {
        anchors.fill: parent
        spacing: 0


        SidebarPanel {
            Layout.preferredWidth: 220
            Layout.fillHeight: true
            onPageRequested: function(p) { appVM.currentPage = p }
            onLogoutRequested: appVM.logout()
        }



        StackLayout {
            id: adminStack
            Layout.fillWidth: true
            Layout.fillHeight: true
            currentIndex: appVM.currentPage

            DashboardHomePage {}
            UsersPage {}
            CupsPage {}
            MatchesPage {}
            TrophiesPage {}
            GroupesPage {}
        }
    }


    Connections {
        target: appVM
        function onCurrentPageChanged() {
            switch (appVM.currentPage) {
                case 0: dashVM.loadStats(); dashVM.loadCups(); break
                case 1: usersVM.loadUsers(); break
                case 2: cupsVM.loadCups(); break
                case 3: matchesVM.loadMatches(); break
                case 4: trophiesVM.loadTrophies(); break
                case 5: groupesVM.loadGroupsForCup(appVM.currentCupId); break
            }
        }
    }
}
