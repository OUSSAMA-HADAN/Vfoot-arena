#include "CupsViewModel.h"
#include "CupRepository.h"

CupsViewModel::CupsViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_cupsModel(new SqlQueryModel(this))
{}

SqlQueryModel* CupsViewModel::cupsModel() const { return m_cupsModel; }

void CupsViewModel::loadCups() {
    m_repo.loadAll(m_cupsModel);
}

int CupsViewModel::cupIdAtRow(int row) const {
    return m_repo.idAtRow(m_cupsModel, row);
}

QString CupsViewModel::cupNameAtRow(int row) const {
    return m_repo.titleAtRow(m_cupsModel, row);
}
