#pragma once
#include "BaseViewModel.h"
#include "SqlQueryModel.h"
#include "TrophyRepository.h"

class TrophiesViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(SqlQueryModel* trophiesModel READ trophiesModel CONSTANT)

public:
    explicit TrophiesViewModel(QObject* parent = nullptr);

    SqlQueryModel* trophiesModel() const;

    Q_INVOKABLE void loadTrophies();

private:
    TrophyRepository m_repo;
    SqlQueryModel*   m_trophiesModel;
};
