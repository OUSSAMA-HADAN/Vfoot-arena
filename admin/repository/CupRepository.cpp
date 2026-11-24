#include "CupRepository.h"
#include "DatabaseManager.h"

void CupRepository::loadAll(SqlQueryModel* model) {
    model->refresh(
        QStringLiteral(
            "SELECT id_cup AS `#`, titre AS `Titre`, visibilite AS `Visibilité`, "
            "statut AS `Statut`, min_players AS `Min`, max_players AS `Max`, "
            "created_at AS `Créé le` "
            "FROM cup ORDER BY created_at DESC"
        ),
        DatabaseManager::instance().database());
}

int CupRepository::idAtRow(SqlQueryModel* model, int row) const {
    if (row < 0 || row >= model->rowCount()) return -1;
    return model->rowData(row).value(0).toInt();
}

QString CupRepository::titleAtRow(SqlQueryModel* model, int row) const {
    if (row < 0 || row >= model->rowCount()) return {};
    return model->rowData(row).value(1).toString();
}
