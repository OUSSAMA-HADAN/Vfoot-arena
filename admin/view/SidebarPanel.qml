import QtQuick
import QtQuick.Layouts

Rectangle {
    id: root
    color: "#070a1f"

    signal pageRequested(int page)
    signal logoutRequested()


    Rectangle {
        anchors { top: parent.top; bottom: parent.bottom; right: parent.right }
        width: 1
        color: "#c9a84c"
        opacity: 0.4
    }

    ColumnLayout {
        anchors { fill: parent; margins: 0 }
        spacing: 0


        Item { height: 28 }

        Text {
            Layout.leftMargin: 20
            text: "⚽  eFoot"
            color: "#c9a84c"
            font.pixelSize: 22
            font.bold: true
        }

        Text {
            Layout.leftMargin: 20
            text: "Administration"
            color: "#6b7ab8"
            font.pixelSize: 11
            bottomPadding: 24
        }


        Text {
            Layout.leftMargin: 20
            text: "NAVIGATION"
            color: "#6b7ab8"
            font.pixelSize: 10
            font.letterSpacing: 1.5
            bottomPadding: 8
        }


        NavButton {
            Layout.fillWidth: true
            icon: "🏠"; label: "Tableau de bord"
            pageIndex: 0; activePage: appVM.currentPage
            onClicked: root.pageRequested(0)
        }
        NavButton {
            Layout.fillWidth: true
            icon: "👥"; label: "Utilisateurs"
            pageIndex: 1; activePage: appVM.currentPage
            onClicked: root.pageRequested(1)
        }
        NavButton {
            Layout.fillWidth: true
            icon: "🏆"; label: "Coupes"
            pageIndex: 2; activePage: appVM.currentPage
            onClicked: root.pageRequested(2)
        }
        NavButton {
            Layout.fillWidth: true
            icon: "⚽"; label: "Matchs"
            pageIndex: 3; activePage: appVM.currentPage
            onClicked: root.pageRequested(3)
        }
        NavButton {
            Layout.fillWidth: true
            icon: "🥇"; label: "Trophées"
            pageIndex: 4; activePage: appVM.currentPage
            onClicked: root.pageRequested(4)
        }


        Item { Layout.fillHeight: true }


        Rectangle {
            Layout.fillWidth: true
            Layout.leftMargin: 16
            Layout.rightMargin: 16
            Layout.bottomMargin: 8
            height: 1
            color: "#2a3170"
        }


        Text {
            Layout.leftMargin: 20
            text: "👤  " + appVM.username
            color: "#a0aec0"
            font.pixelSize: 13
            topPadding: 12
            bottomPadding: 8
            elide: Text.ElideRight
            Layout.rightMargin: 12
        }


        Rectangle {
            Layout.fillWidth: true
            Layout.leftMargin: 16
            Layout.rightMargin: 16
            height: 38
            radius: 8
            color: logoutArea.containsMouse ? "#3d0f0f" : "transparent"
            border.color: "#ff6b6b"
            border.width: 1

            Behavior on color { ColorAnimation { duration: 150 } }

            Text {
                anchors.centerIn: parent
                text: "Déconnexion"
                color: "#ff6b6b"
                font.pixelSize: 13
                font.bold: true
            }

            MouseArea {
                id: logoutArea
                anchors.fill: parent
                hoverEnabled: true
                cursorShape: Qt.PointingHandCursor
                onClicked: root.logoutRequested()
            }
        }

        Item { height: 20 }
    }
}
