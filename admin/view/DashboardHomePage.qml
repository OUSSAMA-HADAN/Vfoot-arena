import QtQuick
import QtQuick.Layouts

Rectangle {

    Layout.fillWidth: true
    Layout.fillHeight: true
    color: "#0a0e27"

    ColumnLayout {
        anchors { fill: parent; margins: 28 }
        spacing: 24


        Text {
            text: "Tableau de bord"
            color: "#ffffff"
            font.pixelSize: 26
            font.bold: true
        }


        RowLayout {
            Layout.fillWidth: true
            spacing: 16

            Repeater {
                model: [
                    { label: "Utilisateurs", icon: "👥", value: dashVM.totalUsers,    accent: "#c9a84c" },
                    { label: "Coupes",        icon: "🏆", value: dashVM.totalCups,     accent: "#c9a84c" },
                    { label: "Matchs",        icon: "⚽", value: dashVM.totalMatches,  accent: "#2ecc71" },
                    { label: "Trophées",      icon: "🥇", value: dashVM.totalTrophies, accent: "#2ecc71" }
                ]

                delegate: Rectangle {
                    Layout.fillWidth: true
                    height: 110
                    radius: 10
                    color: "#1a2150"
                    border.color: modelData.accent
                    border.width: 1

                    ColumnLayout {
                        anchors { fill: parent; margins: 16 }
                        spacing: 4

                        Text {
                            text: modelData.icon + "  " + modelData.label
                            color: "#a0aec0"
                            font.pixelSize: 13
                        }

                        Text {
                            text: modelData.value
                            color: modelData.accent
                            font.pixelSize: 34
                            font.bold: true
                        }
                    }
                }
            }
        }


        RowLayout {
            Layout.fillWidth: true

            Text {
                text: "Coupes récentes"
                color: "#ffffff"
                font.pixelSize: 16
                font.bold: true
                Layout.fillWidth: true
            }


            Rectangle {
                height: 34
                implicitWidth: goText.implicitWidth + 24
                radius: 8
                color: goArea.containsMouse ? "#e0bc5e" : "#c9a84c"
                Behavior on color { ColorAnimation { duration: 150 } }

                Text {
                    id: goText
                    anchors.centerIn: parent
                    text: "Gérer les coupes →"
                    color: "#0a0e27"
                    font.pixelSize: 13
                    font.bold: true
                }

                MouseArea {
                    id: goArea
                    anchors.fill: parent
                    hoverEnabled: true
                    cursorShape: Qt.PointingHandCursor
                    onClicked: appVM.currentPage = 2
                }
            }
        }


        DataTable {
            Layout.fillWidth: true
            Layout.fillHeight: true
            tableModel: dashVM.cupsModel
        }
    }
}
