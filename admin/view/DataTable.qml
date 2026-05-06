import QtQuick
import QtQuick.Controls

Rectangle {
    id: root

    property var tableModel:  null
    property int selectedRow: -1

    signal rowSelected(int row)

    color: "#1a2150"
    radius: 10
    clip: true

    Column {
        anchors.fill: parent


        Rectangle {
            id: headerRect
            width: parent.width
            height: 42
            color: "#0f1435"
            radius: 10

            Rectangle {
                anchors { left: parent.left; right: parent.right; bottom: parent.bottom }
                height: 10
                color: parent.color
            }

            Row {
                anchors { fill: parent; leftMargin: 16; rightMargin: 16 }
                spacing: 0

                Repeater {

                    model: root.tableModel ? root.tableModel.headers : []

                    delegate: Text {
                        width: headerRect.width / Math.max(1, root.tableModel ? root.tableModel.headers.length : 1)
                        height: headerRect.height
                        text: modelData
                        color: "#c9a84c"
                        font.pixelSize: 12
                        font.bold: true
                        verticalAlignment: Text.AlignVCenter
                        elide: Text.ElideRight
                        leftPadding: 4
                    }
                }
            }
        }


        ListView {
            id: listView
            width: parent.width
            height: root.height - headerRect.height
            model: root.tableModel
            clip: true

            ScrollBar.vertical: ScrollBar {
                policy: ScrollBar.AsNeeded
                contentItem: Rectangle {
                    implicitWidth: 4
                    radius: 2
                    color: "#c9a84c"
                }
            }

            delegate: Rectangle {
                id: rowRect
                width: listView.width
                height: 40

                readonly property bool isSelected: root.selectedRow === index
                readonly property var  rowValues:  root.tableModel
                                                   ? root.tableModel.rowData(index)
                                                   : []

                color: isSelected
                       ? Qt.rgba(0.78, 0.66, 0.29, 0.25)
                       : (index % 2 === 0 ? "#1a2150" : "#141840")

                Behavior on color { ColorAnimation { duration: 100 } }

                Rectangle {
                    anchors { left: parent.left; right: parent.right; bottom: parent.bottom }
                    height: 1
                    color: "#2a3170"
                    opacity: 0.5
                }

                Row {
                    anchors { fill: parent; leftMargin: 16; rightMargin: 16 }
                    spacing: 0

                    Repeater {
                        model: rowRect.rowValues

                        delegate: Text {
                            width: rowRect.width / Math.max(1, rowRect.rowValues.length)
                            height: rowRect.height
                            text: modelData !== null && modelData !== undefined
                                  ? modelData.toString() : ""
                            color: "#ffffff"
                            font.pixelSize: 13
                            verticalAlignment: Text.AlignVCenter
                            elide: Text.ElideRight
                            leftPadding: 4
                        }
                    }
                }

                MouseArea {
                    anchors.fill: parent
                    hoverEnabled: true
                    cursorShape: Qt.PointingHandCursor
                    onClicked: {
                        root.selectedRow = index
                        root.rowSelected(index)
                    }
                }
            }

            Connections {
                target: root.tableModel
                function onModelReset() { root.selectedRow = -1 }
            }
        }
    }
}
