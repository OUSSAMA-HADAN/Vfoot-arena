#pragma once
#include <QObject>

class BaseViewModel : public QObject {
    Q_OBJECT
public:
    explicit BaseViewModel(QObject* parent = nullptr);
    ~BaseViewModel() override = default;

protected:

    void emitPropertyChanged(const char* propertyName);
};
