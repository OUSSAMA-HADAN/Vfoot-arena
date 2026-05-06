#include "AppViewModel.h"

AppViewModel::AppViewModel(QObject* parent) : BaseViewModel(parent) {}

bool    AppViewModel::loggedIn()       const { return m_loggedIn; }
bool    AppViewModel::isAdmin()        const { return m_user.role.toLower().contains("admin"); }
QString AppViewModel::username()       const { return m_user.username; }
int     AppViewModel::currentPage()    const { return m_currentPage; }
int     AppViewModel::currentCupId()   const { return m_currentCupId; }
QString AppViewModel::currentCupName() const { return m_currentCupName; }

void AppViewModel::setCurrentPage(int page) {
    if (m_currentPage == page) return;
    m_currentPage = page;
    emit currentPageChanged();
}

void AppViewModel::onLoginSuccess(const User& user) {
    m_user        = user;
    m_loggedIn    = true;
    m_currentPage = 0;
    emit authChanged();
    emit currentPageChanged();
}

void AppViewModel::selectCup(int id, const QString& name) {
    m_currentCupId   = id;
    m_currentCupName = name;
    emit currentCupChanged();
    setCurrentPage(5);
}

void AppViewModel::logout() {
    m_user           = User{};
    m_loggedIn       = false;
    m_currentCupId   = -1;
    m_currentCupName.clear();
    m_currentPage    = 0;
    emit authChanged();
    emit currentPageChanged();
}
