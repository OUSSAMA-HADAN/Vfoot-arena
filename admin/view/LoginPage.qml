import QtQuick
import QtQuick.Controls
import QtQuick.Layouts

Rectangle {
    id: root
    anchors.fill: parent
    color: "#0a0e27"


    ColumnLayout {
        anchors.centerIn: parent
        width: 420
        spacing: 0


        Text {
            Layout.alignment: Qt.AlignHCenter
            text: "⚽  eFoot"
            color: "#c9a84c"
            font.pixelSize: 38
            font.bold: true
            bottomPadding: 6
        }

        Text {
            Layout.alignment: Qt.AlignHCenter
            text: "Panneau d'administration"
            color: "#a0aec0"
            font.pixelSize: 14
            bottomPadding: 28
        }


        Rectangle {
            Layout.fillWidth: true
            color: "#1a2150"
            radius: 16
            border.color: "#c9a84c"
            border.width: 1
            implicitHeight: cardCol.implicitHeight + 48

            ColumnLayout {
                id: cardCol
                anchors {
                    top: parent.top
                    left: parent.left
                    right: parent.right
                    margins: 28
                }
                spacing: 10


                Text {
                    text: "Email"
                    color: "#a0aec0"
                    font.pixelSize: 13
                    topPadding: 4
                }

                Rectangle {
                    Layout.fillWidth: true
                    height: 44
                    radius: 8
                    color: "#0f1435"
                    border.color: emailField.activeFocus ? "#c9a84c" : "#2a3170"
                    border.width: 1

                    TextField {
                        id: emailField
                        anchors { fill: parent; leftMargin: 12; rightMargin: 12 }
                        placeholderText: "votre@email.com"
                        background: Item {}
                        color: "#ffffff"
                        placeholderTextColor: "#6b7ab8"
                        font.pixelSize: 14
                        KeyNavigation.tab: passwordField
                    }
                }


                Text {
                    text: "Mot de passe"
                    color: "#a0aec0"
                    font.pixelSize: 13
                    topPadding: 6
                }

                Rectangle {
                    Layout.fillWidth: true
                    height: 44
                    radius: 8
                    color: "#0f1435"
                    border.color: passwordField.activeFocus ? "#c9a84c" : "#2a3170"
                    border.width: 1

                    TextField {
                        id: passwordField
                        anchors { fill: parent; leftMargin: 12; rightMargin: 12 }
                        placeholderText: "••••••••"
                        echoMode: showPwdCheck.checked
                                  ? TextField.Normal
                                  : TextField.Password
                        background: Item {}
                        color: "#ffffff"
                        placeholderTextColor: "#6b7ab8"
                        font.pixelSize: 14
                        Keys.onReturnPressed: loginVM.login(emailField.text, passwordField.text)
                    }
                }


                Row {
                    spacing: 8
                    topPadding: 2

                    Rectangle {
                        id: showPwdCheck
                        property bool checked: false
                        width: 18; height: 18
                        radius: 4
                        color: checked ? "#c9a84c" : "#0f1435"
                        border.color: "#c9a84c"
                        border.width: 1
                        anchors.verticalCenter: parent.verticalCenter

                        Text {
                            anchors.centerIn: parent
                            text: "✓"
                            color: "#0a0e27"
                            font.pixelSize: 11
                            font.bold: true
                            visible: showPwdCheck.checked
                        }

                        MouseArea {
                            anchors.fill: parent
                            hoverEnabled: true
                            cursorShape: Qt.PointingHandCursor
                            onClicked: showPwdCheck.checked = !showPwdCheck.checked
                        }
                    }

                    Text {
                        text: "Afficher le mot de passe"
                        color: "#a0aec0"
                        font.pixelSize: 13
                        anchors.verticalCenter: parent.verticalCenter
                    }
                }


                Rectangle {
                    Layout.fillWidth: true
                    height: 38
                    radius: 8
                    visible: loginVM.statusMessage !== ""
                    color: loginVM.isError ? "#2a1515" : "#152a1e"
                    border.color: loginVM.isError ? "#ff6b6b" : "#2ecc71"
                    border.width: 1

                    Text {
                        anchors.centerIn: parent
                        text: loginVM.statusMessage
                        color: loginVM.isError ? "#ff6b6b" : "#2ecc71"
                        font.pixelSize: 13
                        horizontalAlignment: Text.AlignHCenter
                        wrapMode: Text.WordWrap
                        width: parent.width - 16
                    }
                }


                Rectangle {
                    Layout.fillWidth: true
                    height: 44
                    radius: 8
                    color: loginBtnArea.pressed
                           ? "#b8962e"
                           : loginBtnArea.containsMouse ? "#e0bc5e" : "#c9a84c"

                    Behavior on color { ColorAnimation { duration: 150 } }

                    Text {
                        anchors.centerIn: parent
                        text: loginVM.isBusy ? "Connexion…" : "Se connecter"
                        color: "#0a0e27"
                        font.pixelSize: 15
                        font.bold: true
                    }

                    MouseArea {
                        id: loginBtnArea
                        anchors.fill: parent
                        hoverEnabled: true
                        cursorShape: Qt.PointingHandCursor
                        enabled: !loginVM.isBusy
                        onClicked: loginVM.login(emailField.text, passwordField.text)
                    }
                }

                Item { height: 4 }
            }
        }
    }


    Connections {
        target: loginVM
        function onLoginFailed() { passwordField.clear() }
    }
}
