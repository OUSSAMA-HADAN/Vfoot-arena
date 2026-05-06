import QtQuick
import QtQuick.Layouts

Rectangle {
    Layout.fillWidth: true
    Layout.fillHeight: true
    color: "#0a0e27"

    property int selRow: -1

    ColumnLayout {
        anchors { fill: parent; margins: 28 }
        spacing: 16


        RowLayout {
            Layout.fillWidth: true

            Text {
                text: "Coupes"
                color: "#ffffff"; font.pixelSize: 26; font.bold: true
                Layout.fillWidth: true
            }

            Rectangle {
                height: 34; width: 34; radius: 8
                color: refreshCupsArea.containsMouse ? "#2a3170" : "#1a2150"
                border.color: "#2a3170"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text { anchors.centerIn: parent; text: "↺"; color: "#c9a84c"; font.pixelSize: 18 }
                MouseArea {
                    id: refreshCupsArea
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    onClicked: cupsVM.loadCups()
                }
            }
        }


        DataTable {
            id: cupsTable
            Layout.fillWidth: true
            Layout.fillHeight: true
            tableModel: cupsVM.cupsModel
            onRowSelected: function(row) { selRow = row }
        }


        Rectangle {
            height: 42; Layout.fillWidth: true; radius: 8
            opacity: selRow >= 0 ? 1.0 : 0.4
            color: groupsArea.containsMouse && selRow >= 0 ? "#e0bc5e" : "#c9a84c"
            Behavior on color { ColorAnimation { duration: 150 } }

            Text {
                anchors.centerIn: parent
                text: "👁  Voir les groupes de cette coupe →"
                color: "#0a0e27"; font.pixelSize: 14; font.bold: true
            }

            MouseArea {
                id: groupsArea
                anchors.fill: parent
                hoverEnabled: true
                cursorShape: selRow >= 0 ? Qt.PointingHandCursor : Qt.ArrowCursor
                enabled: selRow >= 0
                onClicked: appVM.selectCup(
                    cupsVM.cupIdAtRow(selRow),
                    cupsVM.cupNameAtRow(selRow)
                )
            }
        }
    }
}
