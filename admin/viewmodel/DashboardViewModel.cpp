#include "DashboardViewModel.h"
#include "DatabaseManager.h"
#include <QSqlQuery>

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
    m_cupsModel->refresh(
        QStringLiteral("SELECT * FROM cup ORDER BY created_at DESC"),
        DatabaseManager::instance().database());
}

void DashboardViewModel::loadStats() {
    QSqlQuery q(DatabaseManager::instance().database());

    q.exec(QStringLiteral("SELECT COUNT(*) FROM users"));
    if (q.next()) m_totalUsers = q.value(0).toInt();

    q.exec(QStringLiteral("SELECT COUNT(*) FROM cup"));
    if (q.next()) m_totalCups = q.value(0).toInt();

    q.exec(QStringLiteral("SELECT COUNT(*) FROM matche"));
    if (q.next()) m_totalMatches = q.value(0).toInt();

    q.exec(QStringLiteral("SELECT COUNT(*) FROM trophee"));
    if (q.next()) m_totalTrophies = q.value(0).toInt();

    emit statsLoaded();
}
