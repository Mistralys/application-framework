<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method MediaRecord[] getItemsObjects()
 */
class MediaFilterCriteria extends DBHelper_BaseFilterCriteria
{
    public const FILTER_EXTENSIONS = 'extensions';

    public function selectExtensions(array $extensions) : self
    {
        foreach($extensions as $extension) {
            $this->selectExtension($extension);
        }

        return $this;
    }

    public function selectExtension(string $extension) : self
    {
        return $this->selectCriteriaValue(self::FILTER_EXTENSIONS, $extension);
    }

    protected function prepareQuery(): void
    {
        $this->addWhereColumnIN(MediaCollection::COL_EXTENSION, $this->getCriteriaValues(self::FILTER_EXTENSIONS));
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }
}
