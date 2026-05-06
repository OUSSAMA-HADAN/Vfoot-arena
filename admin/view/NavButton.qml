import QtQuick

Rectangle {
    id: root

    property string icon:       ""
    property string label:      ""
    property int    pageIndex:  -1
    property int    activePage: -1

    readonly property bool isActive: activePage === pageIndex

    signal clicked()

    height: 42
    radius: 8
    color: isActive
           ? Qt.rgba(0.78, 0.66, 0.29, 0.18)
           : hoverArea.containsMouse ? Qt.rgba(1,1,1,0.04) : "transparent"

    Behavior on color { ColorAnimation { duration: 120 } }


    Rectangle {
        anchors { left: parent.left; top: parent.top; bottom: parent.bottom }
        width: 3
        radius: 2
        color: "#c9a84c"
        visible: isActive
    }

    Row {
        anchors { left: parent.left; leftMargin: isActive ? 18 : 14; verticalCenter: parent.verticalCenter }
        spacing: 10

        Text {
            text: root.icon
            font.pixelSize: 16
        }

        Text {
            text: root.label
            color: (isActive || hoverArea.containsMouse) ? "#c9a84c" : "#a0aec0"
            font.pixelSize: 14
            font.bold: isActive

            Behavior on color { ColorAnimation { duration: 120 } }
        }
    }

    MouseArea {
        id: hoverArea
        anchors.fill: parent
        hoverEnabled: true
        cursorShape: Qt.PointingHandCursor
        onClicked: root.clicked()
    }
}
