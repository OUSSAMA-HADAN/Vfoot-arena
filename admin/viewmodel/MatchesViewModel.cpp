#include "MatchesViewModel.h"
#include "MatchRepository.h"

MatchesViewModel::MatchesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_matchesModel(new SqlQueryModel(this))
{}

SqlQueryModel* MatchesViewModel::matchesModel() const { return m_matchesModel; }

void MatchesViewModel::loadMatches() {
    m_repo.loadAll(m_matchesModel);
}
