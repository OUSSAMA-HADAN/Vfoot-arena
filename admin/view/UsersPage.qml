import QtQuick
import QtQuick.Controls
import QtQuick.Layouts

Rectangle {
    Layout.fillWidth: true
    Layout.fillHeight: true
    color: "#0a0e27"


    property int  selRow:    -1
    property int  selUserId: -1
    property bool selBanned: false


    Popup {
        id: namePopup
        anchors.centerIn: Overlay.overlay
        width: 360; height: 160
        modal: true
        padding: 0

        background: Rectangle {
            color: "#1a2150"; radius: 12
            border.color: "#2a3170"; border.width: 1
        }

        ColumnLayout {
            anchors { fill: parent; margins: 20 }
            spacing: 12

            Text { text: "Changer le nom"; color: "#ffffff"; font.pixelSize: 15; font.bold: true }

            Rectangle {
                Layout.fillWidth: true; height: 40; radius: 8
                color: "#0f1435"; border.color: "#2a3170"; border.width: 1

                TextField {
                    id: nameField
                    anchors { fill: parent; leftMargin: 10; rightMargin: 10 }
                    background: Item {}
                    color: "#ffffff"; placeholderTextColor: "#6b7ab8"
                    font.pixelSize: 14
                }
            }

            RowLayout {
                spacing: 10

                Rectangle {
                    Layout.fillWidth: true; height: 36; radius: 8
                    color: "#2a3170"
                    Text { anchors.centerIn: parent; text: "Annuler"; color: "#ffffff"; font.pixelSize: 13 }
                    MouseArea { anchors.fill: parent; cursorShape: Qt.PointingHandCursor; onClicked: namePopup.close() }
                }

                Rectangle {
                    Layout.fillWidth: true; height: 36; radius: 8
                    color: "#c9a84c"
                    Text { anchors.centerIn: parent; text: "Valider"; color: "#0a0e27"; font.pixelSize: 13; font.bold: true }
                    MouseArea {
                        anchors.fill: parent; cursorShape: Qt.PointingHandCursor
                        onClicked: {
                            usersVM.changeName(selUserId, nameField.text)
                            namePopup.close()
                        }
                    }
                }
            }
        }
    }


    Popup {
        id: emailPopup
        anchors.centerIn: Overlay.overlay
        width: 360; height: 160
        modal: true
        padding: 0

        background: Rectangle {
            color: "#1a2150"; radius: 12
            border.color: "#2a3170"; border.width: 1
        }

        ColumnLayout {
            anchors { fill: parent; margins: 20 }
            spacing: 12

            Text { text: "Changer l'email"; color: "#ffffff"; font.pixelSize: 15; font.bold: true }

            Rectangle {
                Layout.fillWidth: true; height: 40; radius: 8
                color: "#0f1435"; border.color: "#2a3170"; border.width: 1

                TextField {
                    id: emailPopupField
                    anchors { fill: parent; leftMargin: 10; rightMargin: 10 }
                    background: Item {}
                    color: "#ffffff"; placeholderTextColor: "#6b7ab8"
                    font.pixelSize: 14
                }
            }

            RowLayout {
                spacing: 10

                Rectangle {
                    Layout.fillWidth: true; height: 36; radius: 8
                    color: "#2a3170"
                    Text { anchors.centerIn: parent; text: "Annuler"; color: "#ffffff"; font.pixelSize: 13 }
                    MouseArea { anchors.fill: parent; cursorShape: Qt.PointingHandCursor; onClicked: emailPopup.close() }
                }

                Rectangle {
                    Layout.fillWidth: true; height: 36; radius: 8
                    color: "#c9a84c"
                    Text { anchors.centerIn: parent; text: "Valider"; color: "#0a0e27"; font.pixelSize: 13; font.bold: true }
                    MouseArea {
                        anchors.fill: parent; cursorShape: Qt.PointingHandCursor
                        onClicked: {
                            usersVM.changeEmail(selUserId, emailPopupField.text)
                            emailPopup.close()
                        }
                    }
                }
            }
        }
    }


    ColumnLayout {
        anchors { fill: parent; margins: 28 }
        spacing: 16


        RowLayout {
            Layout.fillWidth: true

            Text {
                text: "Utilisateurs"
                color: "#ffffff"; font.pixelSize: 26; font.bold: true
                Layout.fillWidth: true
            }


            Rectangle {
                height: 34; width: 34; radius: 8
                color: refreshArea.containsMouse ? "#2a3170" : "#1a2150"
                border.color: "#2a3170"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text { anchors.centerIn: parent; text: "↺"; color: "#c9a84c"; font.pixelSize: 18 }
                MouseArea {
                    id: refreshArea
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    onClicked: usersVM.loadUsers()
                }
            }
        }


        Rectangle {
            Layout.fillWidth: true; height: 36; radius: 8
            visible: usersVM.statusMsg !== ""
            color: usersVM.statusError ? "#2a1515" : "#152a1e"
            border.color: usersVM.statusError ? "#ff6b6b" : "#2ecc71"; border.width: 1

            Text {
                anchors.centerIn: parent
                text: usersVM.statusMsg
                color: usersVM.statusError ? "#ff6b6b" : "#2ecc71"; font.pixelSize: 13
            }


            Timer {
                running: usersVM.statusMsg !== ""
                interval: 4000
                onTriggered: usersVM.clearStatus()
            }
        }


        DataTable {
            id: usersTable
            Layout.fillWidth: true
            Layout.fillHeight: true
            tableModel: usersVM.usersModel

            onRowSelected: function(row) {
                selRow    = row
                selUserId = usersVM.userIdAtRow(row)
                selBanned = usersVM.userIsBannedById(selUserId)
            }
        }


        RowLayout {
            Layout.fillWidth: true
            spacing: 10
            enabled: selRow >= 0


            Rectangle {
                height: 38; implicitWidth: 160; radius: 8
                opacity: selRow >= 0 ? 1 : 0.4
                color: renameArea.containsMouse ? "#2a3170" : "#1a2150"
                border.color: "#c9a84c"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text { anchors.centerIn: parent; text: "✏  Changer le nom"; color: "#c9a84c"; font.pixelSize: 13 }
                MouseArea {
                    id: renameArea
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    enabled: selRow >= 0
                    onClicked: {
                        nameField.text = usersVM.userNameAtRow(selRow)
                        namePopup.open()
                    }
                }
            }


            Rectangle {
                height: 38; implicitWidth: 170; radius: 8
                opacity: selRow >= 0 ? 1 : 0.4
                color: emailArea.containsMouse ? "#2a3170" : "#1a2150"
                border.color: "#c9a84c"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text { anchors.centerIn: parent; text: "✉  Changer l'email"; color: "#c9a84c"; font.pixelSize: 13 }
                MouseArea {
                    id: emailArea
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    enabled: selRow >= 0
                    onClicked: {
                        emailPopupField.text = usersVM.userEmailAtRow(selRow)
                        emailPopup.open()
                    }
                }
            }


            Rectangle {
                height: 38; implicitWidth: 120; radius: 8
                opacity: selRow >= 0 ? 1 : 0.4
                color: banArea.containsMouse
                       ? (selBanned ? "#1e4d1e" : "#4d1e1e")
                       : (selBanned ? "#152a15" : "#2a1515")
                border.color: selBanned ? "#2ecc71" : "#ff6b6b"; border.width: 1
                Behavior on color { ColorAnimation { duration: 120 } }
                Text {
                    anchors.centerIn: parent
                    text: selBanned ? "✔  Débannir" : "⊘  Bannir"
                    color: selBanned ? "#2ecc71" : "#ff6b6b"; font.pixelSize: 13; font.bold: true
                }
                MouseArea {
                    id: banArea
                    anchors.fill: parent; hoverEnabled: true; cursorShape: Qt.PointingHandCursor
                    enabled: selRow >= 0
                    onClicked: {
                        usersVM.toggleBan(selUserId, !selBanned)
                        selBanned = !selBanned
                    }
                }
            }

            Item { Layout.fillWidth: true }
        }
    }
}
