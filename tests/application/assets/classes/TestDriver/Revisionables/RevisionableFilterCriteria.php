<?php

declare(strict_types=1);

namespace TestDriver\Revisionables;

use Application\Revisionable\Collection\BaseRevisionableFilterCriteria;
use Application\Revisionable\StatusHandling\StandardStateSetupFilterInterface;
use Application\Revisionable\StatusHandling\StandardStateSetupFilterTrait;
use DBHelper_StatementBuilder_ValuesContainer;

class RevisionableFilterCriteria
    extends BaseRevisionableFilterCriteria
    implements StandardStateSetupFilterInterface
{
    use StandardStateSetupFilterTrait;

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }

    protected function _initCustomColumns(): void
    {
    }
}
