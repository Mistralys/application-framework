<?php

declare(strict_types=1);

namespace Application\Messaging;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @property MessagingCollection $collection
 */
class MessagingFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public function __construct(MessagingCollection $collection)
    {
        parent::__construct($collection);
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
