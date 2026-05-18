#include "MatchesViewModel.h"
#include "DatabaseManager.h"

MatchesViewModel::MatchesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_matchesModel(new SqlQueryModel(this))
{}

SqlQueryModel* MatchesViewModel::matchesModel() const { return m_matchesModel; }

void MatchesViewModel::loadMatches() {
    m_matchesModel->refresh(
        QStringLiteral(
            "SELECT "
            "  m.id_match        AS `#`, "
            "  c.titre           AS `Coupe`, "
            "  hu.username       AS `Domicile`, "
            "  au.username       AS `Extérieur`, "
            "  m.round           AS `Round`, "
            "  m.leg             AS `Leg`, "
            "  m.statut          AS `Statut`, "
            "  m.deadline        AS `Deadline` "
            "FROM matche m "
            "LEFT JOIN cup   c  ON m.id_cup       = c.id_cup "
            "LEFT JOIN users hu ON m.home_user_id = hu.id_user "
            "LEFT JOIN users au ON m.away_user_id = au.id_user "
            "ORDER BY m.id_match DESC"
        ),
        DatabaseManager::instance().database());
}
