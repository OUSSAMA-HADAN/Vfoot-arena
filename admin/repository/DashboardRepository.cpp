#include "DashboardRepository.h"
#include "DatabaseManager.h"
#include <QSqlQuery>

void DashboardRepository::loadCups(SqlQueryModel* model) {
    model->refresh(
        QStringLiteral(
            "SELECT id_cup AS `#`, titre AS `Titre`, visibilite AS `Visibilité`, "
            "statut AS `Statut`, created_at AS `Créé le` "
            "FROM cup ORDER BY created_at DESC"
        ),
        DatabaseManager::instance().database());
}

int DashboardRepository::queryCount(const QString& sql) const {
    QSqlQuery q(DatabaseManager::instance().database());
    q.exec(sql);
    return q.next() ? q.value(0).toInt() : 0;
}

int DashboardRepository::countUsers()    const { return queryCount(QStringLiteral("SELECT COUNT(*) FROM users")); }
int DashboardRepository::countCups()     const { return queryCount(QStringLiteral("SELECT COUNT(*) FROM cup")); }
int DashboardRepository::countMatches()  const { return queryCount(QStringLiteral("SELECT COUNT(*) FROM matche")); }
int DashboardRepository::countTrophies() const { return queryCount(QStringLiteral("SELECT COUNT(*) FROM trophee")); }
