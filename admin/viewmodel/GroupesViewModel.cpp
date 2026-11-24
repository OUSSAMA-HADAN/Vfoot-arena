#include "GroupesViewModel.h"
#include "GroupeRepository.h"

GroupesViewModel::GroupesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_groupesModel(new SqlQueryModel(this))
    , m_standingsModel(new SqlQueryModel(this))
{}

SqlQueryModel* GroupesViewModel::groupesModel()   const { return m_groupesModel; }
SqlQueryModel* GroupesViewModel::standingsModel() const { return m_standingsModel; }

void GroupesViewModel::loadGroupsForCup(int cupId) {
    m_repo.loadForCup(cupId, m_groupesModel);
}

void GroupesViewModel::loadStandings(int groupId) {
    m_repo.loadStandings(groupId, m_standingsModel);
}

int GroupesViewModel::groupIdAtRow(int row) const {
    return m_repo.idAtRow(m_groupesModel, row);
}
