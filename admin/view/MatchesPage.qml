import QtQuick
import QtQuick.Layouts

Rectangle {
    Layout.fillWidth: true
    Layout.fillHeight: true
    color: "#0a0e27"

    ColumnLayout {
        anchors { fill: parent; margins: 28 }
        spacing: 16

        RowLayout {
            Layout.fillWidth: true

            Text {
                text: "Matchs"
                color: "#ffffff"; font.pixelSize: 26; font.bold: true
                Layout.fillWidth: true
            }

            Rectangle {
                height: 34; width: 34; radius: 8
                color: refreshM.containsMouse ? "#2a3170" : "#1a2150"
                border.color: "#2a3170"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text { anchors.centerIn: parent; text: "↺"; color: "#c9a84c"; font.pixelSize: 18 }
                MouseArea {
                    id: refreshM
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    onClicked: matchesVM.loadMatches()
                }
            }
        }

        DataTable {
            Layout.fillWidth: true
            Layout.fillHeight: true
            tableModel: matchesVM.matchesModel
        }
    }
}
