#pragma once
#include "BaseViewModel.h"
#include "SqlQueryModel.h"

class MatchesViewModel : public BaseViewModel {
    Q_OBJECT

    Q_PROPERTY(SqlQueryModel* matchesModel READ matchesModel CONSTANT)

public:
    explicit MatchesViewModel(QObject* parent = nullptr);

    SqlQueryModel* matchesModel() const;

    Q_INVOKABLE void loadMatches();

private:
    SqlQueryModel* m_matchesModel;
};
