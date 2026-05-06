#include "BaseViewModel.h"
#include <QMetaObject>
#include <QMetaProperty>

BaseViewModel::BaseViewModel(QObject* parent) : QObject(parent) {}

void BaseViewModel::emitPropertyChanged(const char* propertyName) {
    int idx = metaObject()->indexOfProperty(propertyName);
    if (idx < 0) return;
    QMetaProperty prop = metaObject()->property(idx);
    if (prop.hasNotifySignal())
        prop.notifySignal().invoke(this, Qt::DirectConnection);
}
