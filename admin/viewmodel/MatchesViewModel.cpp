#include "MatchesViewModel.h"
#include "DatabaseManager.h"

MatchesViewModel::MatchesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_matchesModel(new SqlQueryModel(this))
{}

SqlQueryModel* MatchesViewModel::matchesModel() const { return m_matchesModel; }

void MatchesViewModel::loadMatches() {
    m_matchesModel->refresh(
        QStringLiteral("SELECT * FROM matche ORDER BY id_match DESC"),
        DatabaseManager::instance().database());
}
