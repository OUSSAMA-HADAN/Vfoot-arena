#include "TrophiesViewModel.h"
#include "DatabaseManager.h"

TrophiesViewModel::TrophiesViewModel(QObject* parent)
    : BaseViewModel(parent)
    , m_trophiesModel(new SqlQueryModel(this))
{}

SqlQueryModel* TrophiesViewModel::trophiesModel() const { return m_trophiesModel; }

void TrophiesViewModel::loadTrophies() {
    m_trophiesModel->refresh(
        QStringLiteral("SELECT * FROM trophee"),
        DatabaseManager::instance().database());
}
