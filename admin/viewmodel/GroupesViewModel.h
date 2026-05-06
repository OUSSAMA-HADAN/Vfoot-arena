#pragma once
#include "BaseViewModel.h"
#include "SqlQueryModel.h"

class GroupesViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(SqlQueryModel* groupesModel   READ groupesModel   CONSTANT)
    Q_PROPERTY(SqlQueryModel* standingsModel READ standingsModel CONSTANT)

public:
    explicit GroupesViewModel(QObject* parent = nullptr);

    SqlQueryModel* groupesModel()   const;
    SqlQueryModel* standingsModel() const;

    Q_INVOKABLE void loadGroupsForCup(int cupId);
    Q_INVOKABLE void loadStandings(int groupId);
    Q_INVOKABLE int  groupIdAtRow(int row) const;

private:
    SqlQueryModel* m_groupesModel;
    SqlQueryModel* m_standingsModel;
};
