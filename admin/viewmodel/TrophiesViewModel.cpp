#include "TrophiesViewModel.h"
#include "TrophyRepository.h"

TrophiesViewModel::TrophiesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_trophiesModel(new SqlQueryModel(this))
{}

SqlQueryModel* TrophiesViewModel::trophiesModel() const { return m_trophiesModel; }

void TrophiesViewModel::loadTrophies() {
    m_repo.loadAll(m_trophiesModel);
}
