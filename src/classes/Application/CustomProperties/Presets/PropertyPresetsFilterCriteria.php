<?php

declare(strict_types=1);


namespace Application\CustomProperties\Presets;
use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

class PropertyPresetsFilterCriteria extends DBHelper_BaseFilterCriteria
{
    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }

    protected function _registerJoins(): void
    {
    }
}
