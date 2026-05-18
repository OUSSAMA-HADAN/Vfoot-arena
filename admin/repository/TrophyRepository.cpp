#include "TrophyRepository.h"
#include "DatabaseManager.h"

void TrophyRepository::loadAll(SqlQueryModel* model) {
    model->refresh(
        QStringLiteral(
            "SELECT "
            "  t.id_trophee  AS `#`, "
            "  t.nom         AS `Trophée`, "
            "  t.description AS `Description`, "
            "  c.titre       AS `Coupe` "
            "FROM trophee t "
            "LEFT JOIN cup c ON t.id_cup = c.id_cup "
            "ORDER BY t.id_trophee DESC"
        ),
        DatabaseManager::instance().database());
}
