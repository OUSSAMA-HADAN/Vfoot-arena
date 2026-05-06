#pragma once
#include "BaseViewModel.h"
#include "SqlQueryModel.h"
#include <QString>

class CupsViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(SqlQueryModel* cupsModel READ cupsModel CONSTANT)

public:
    explicit CupsViewModel(QObject* parent = nullptr);

    SqlQueryModel* cupsModel() const;

    Q_INVOKABLE void    loadCups();
    Q_INVOKABLE int     cupIdAtRow(int row)   const;
    Q_INVOKABLE QString cupNameAtRow(int row) const;

private:
    SqlQueryModel* m_cupsModel;
};
