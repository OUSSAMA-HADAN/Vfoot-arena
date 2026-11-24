#include "GroupeRepository.h"
#include "DatabaseManager.h"
#include <QSqlQuery>
#include <QSqlError>
#include <QDebug>

void GroupeRepository::loadForCup(int cupId, SqlQueryModel* model) {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral("SELECT * FROM groupe WHERE id_cup = ?"));
    q.addBindValue(cupId);
    if (!q.exec()) qWarning() << "[GroupeRepository]" << q.lastError().text();
    model->refresh(std::move(q));
}

void GroupeRepository::loadStandings(int groupId, SqlQueryModel* model) {
    QSqlQuery q(DatabaseManager::instance().database());
    q.prepare(QStringLiteral(
        "SELECT u.username      AS Joueur, "
        "       e.played        AS J, "
        "       e.wins          AS V, "
        "       e.draws         AS N, "
        "       e.losses        AS D, "
        "       e.goals_for     AS BP, "
        "       e.goals_against AS BC, "
        "       e.points        AS Pts "
        "FROM est_classe_dans e "
        "JOIN users u ON e.id_user = u.id_user "
        "WHERE e.id_groupe = ? "
        "ORDER BY e.points DESC, e.goals_for DESC"
    ));
    q.addBindValue(groupId);
    if (!q.exec()) qWarning() << "[GroupeRepository]" << q.lastError().text();
    model->refresh(std::move(q));
}

int GroupeRepository::idAtRow(SqlQueryModel* model, int row) const {
    if (row < 0 || row >= model->rowCount()) return -1;
    return model->rowData(row).value(0).toInt();
}
