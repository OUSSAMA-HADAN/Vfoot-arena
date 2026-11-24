#include "DashboardViewModel.h"
#include "DashboardRepository.h"

DashboardViewModel::DashboardViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_cupsModel(new SqlQueryModel(this))
{}

SqlQueryModel* DashboardViewModel::cupsModel()      const { return m_cupsModel; }
int            DashboardViewModel::totalUsers()     const { return m_totalUsers; }
int            DashboardViewModel::totalCups()      const { return m_totalCups; }
int            DashboardViewModel::totalMatches()   const { return m_totalMatches; }
int            DashboardViewModel::totalTrophies()  const { return m_totalTrophies; }

void DashboardViewModel::loadCups() {
    m_repo.loadCups(m_cupsModel);
}

void DashboardViewModel::loadStats() {
    m_totalUsers    = m_repo.countUsers();
    m_totalCups     = m_repo.countCups();
    m_totalMatches  = m_repo.countMatches();
    m_totalTrophies = m_repo.countTrophies();
    emit statsLoaded();
}
