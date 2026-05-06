#include "CupsViewModel.h"
#include "DatabaseManager.h"

CupsViewModel::CupsViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_cupsModel(new SqlQueryModel(this))
{}

SqlQueryModel* CupsViewModel::cupsModel() const { return m_cupsModel; }

void CupsViewModel::loadCups() {
    m_cupsModel->refresh(
        QStringLiteral("SELECT * FROM cup ORDER BY created_at DESC"),
        DatabaseManager::instance().database());
}

int CupsViewModel::cupIdAtRow(int row) const {
    if (row < 0 || row >= m_cupsModel->rowCount()) return -1;
    return m_cupsModel->rowData(row).value(0).toInt();
}

QString CupsViewModel::cupNameAtRow(int row) const {
    if (row < 0 || row >= m_cupsModel->rowCount()) return {};
    return m_cupsModel->rowData(row).value(1).toString();
}
