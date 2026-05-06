import QtQuick
import QtQuick.Layouts

Rectangle {
    Layout.fillWidth: true
    Layout.fillHeight: true
    color: "#0a0e27"

    property int selGroupRow: -1

    ColumnLayout {
        anchors { fill: parent; margins: 28 }
        spacing: 16


        RowLayout {
            Layout.fillWidth: true


            Rectangle {
                height: 34; implicitWidth: backText.implicitWidth + 24; radius: 8
                color: backArea.containsMouse ? "#2a3170" : "#1a2150"
                border.color: "#2a3170"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text { id: backText; anchors.centerIn: parent; text: "← Retour"; color: "#a0aec0"; font.pixelSize: 13 }
                MouseArea {
                    id: backArea
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    onClicked: appVM.currentPage = 2
                }
            }

            Text {
                text: "Groupes — " + appVM.currentCupName
                color: "#ffffff"; font.pixelSize: 22; font.bold: true
                Layout.fillWidth: true; leftPadding: 12
                elide: Text.ElideRight
            }
        }


        Text {
            text: "Groupes de la coupe"
            color: "#a0aec0"; font.pixelSize: 13; font.bold: true
        }

        DataTable {
            id: groupsTable
            Layout.fillWidth: true
            height: 180
            tableModel: groupesVM.groupesModel

            onRowSelected: function(row) {
                selGroupRow = row
                groupesVM.loadStandings(groupesVM.groupIdAtRow(row))
            }
        }


        Text {
            text: "Classement du groupe"
            color: "#a0aec0"; font.pixelSize: 13; font.bold: true
        }


        Rectangle {
            Layout.fillWidth: true
            height: 60
            radius: 8
            color: "#1a2150"
            border.color: "#2a3170"; border.width: 1
            visible: selGroupRow < 0

            Text {
                anchors.centerIn: parent
                text: "← Sélectionnez un groupe pour voir son classement"
                color: "#6b7ab8"; font.pixelSize: 13
            }
        }

        DataTable {
            Layout.fillWidth: true
            Layout.fillHeight: true
            tableModel: groupesVM.standingsModel
            visible: selGroupRow >= 0
        }
    }
}
