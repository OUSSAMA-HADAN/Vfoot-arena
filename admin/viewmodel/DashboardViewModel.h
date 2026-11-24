#pragma once
#include "BaseViewModel.h"
#include "SqlQueryModel.h"
#include "DashboardRepository.h"

class DashboardViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(SqlQueryModel* cupsModel      READ cupsModel      CONSTANT)
    Q_PROPERTY(int totalUsers    READ totalUsers    NOTIFY statsLoaded)
    Q_PROPERTY(int totalCups     READ totalCups     NOTIFY statsLoaded)
    Q_PROPERTY(int totalMatches  READ totalMatches  NOTIFY statsLoaded)
    Q_PROPERTY(int totalTrophies READ totalTrophies NOTIFY statsLoaded)

public:
    explicit DashboardViewModel(QObject* parent = nullptr);

    SqlQueryModel* cupsModel()      const;
    int            totalUsers()     const;
    int            totalCups()      const;
    int            totalMatches()   const;
    int            totalTrophies()  const;

    Q_INVOKABLE void loadStats();
    Q_INVOKABLE void loadCups();

signals:
    void statsLoaded();

private:
    DashboardRepository m_repo;
    SqlQueryModel*      m_cupsModel;
    int m_totalUsers    = 0;
    int m_totalCups     = 0;
    int m_totalMatches  = 0;
    int m_totalTrophies = 0;
};
