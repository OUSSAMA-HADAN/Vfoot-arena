#include "GroupesViewModel.h"
#include "DatabaseManager.h"

GroupesViewModel::GroupesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_groupesModel(new SqlQueryModel(this))
    , m_standingsModel(new SqlQueryModel(this))
{}

SqlQueryModel* GroupesViewModel::groupesModel()   const { return m_groupesModel; }
SqlQueryModel* GroupesViewModel::standingsModel() const { return m_standingsModel; }

void GroupesViewModel::loadGroupsForCup(int cupId) {
    m_groupesModel->refresh(
        DatabaseManager::buildSql(
            QStringLiteral("SELECT * FROM groupe WHERE id_cup = ?"), {cupId}),
        DatabaseManager::instance().database());
}

void GroupesViewModel::loadStandings(int groupId) {
    const QString sql = DatabaseManager::buildSql(
        QStringLiteral(
            "SELECT u.username   AS Joueur, "
            "       e.played     AS J, "
            "       e.wins       AS V, "
            "       e.draws      AS N, "
            "       e.losses     AS D, "
            "       e.goals_for  AS BP, "
            "       e.goals_against AS BC, "
            "       e.points     AS Pts "
            "FROM est_classe_dans e "
            "JOIN users u ON e.id_user = u.id_user "
            "WHERE e.id_groupe = ? "
            "ORDER BY e.points DESC, e.goals_for DESC"
        ),
        {groupId}
    );
    m_standingsModel->refresh(sql, DatabaseManager::instance().database());
}

int GroupesViewModel::groupIdAtRow(int row) const {
    if (row < 0 || row >= m_groupesModel->rowCount()) return -1;
    return m_groupesModel->rowData(row).value(0).toInt();
}
