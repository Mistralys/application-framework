<?php

declare(strict_types=1);

namespace Application\AppSets;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

final class AppSetsFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public function __construct(AppSetsCollection $collection)
    {
        parent::__construct($collection);
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
        self::fillValues($container);
    }

    public static function getValues() : DBHelper_StatementBuilder_ValuesContainer
    {
        return self::fillValues(statementValues());
    }

    public static function fillValues(DBHelper_StatementBuilder_ValuesContainer $container) : DBHelper_StatementBuilder_ValuesContainer
    {
        return $container
            ->table('{set_table}', AppSetsCollection::TABLE_NAME)
            ->field('{set_primary}', AppSetsCollection::PRIMARY_NAME)
            ->field('{col_alias}', AppSetsCollection::COL_ALIAS)
            ->field('{col_label}', AppSetsCollection::COL_LABEL)
            ->field('{col_description}', AppSetsCollection::COL_DESCRIPTION)
            ->field('{col_is_active}', AppSetsCollection::COL_IS_ACTIVE)
            ->field('{col_url_names}', AppSetsCollection::COL_URL_NAMES);
    }
}
