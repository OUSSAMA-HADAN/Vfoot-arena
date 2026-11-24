#pragma once
#include "SqlQueryModel.h"

class GroupeRepository {
public:
    void loadForCup(int cupId, SqlQueryModel* model);
    void loadStandings(int groupId, SqlQueryModel* model);
    int  idAtRow(SqlQueryModel* model, int row) const;
};
