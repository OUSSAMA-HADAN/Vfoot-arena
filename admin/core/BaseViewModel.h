#pragma once
#include <QObject>

class BaseViewModel : public QObject {
    Q_OBJECT
public:
    explicit BaseViewModel(QObject* parent = nullptr);

protected:

    void emitPropertyChanged(const char* propertyName);
};
