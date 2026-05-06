import QtQuick
import QtQuick.Controls

ApplicationWindow {
    id: root
    visible: true
    width: 1200
    height: 760
    minimumWidth: 900
    minimumHeight: 600
    title: "eFoot Admin"
    color: "#0a0e27"



    Loader {
        id: pageLoader
        anchors.fill: parent
        source: appVM.loggedIn
            ? Qt.resolvedUrl("DashboardShell.qml")
            : Qt.resolvedUrl("LoginPage.qml")
    }
}
