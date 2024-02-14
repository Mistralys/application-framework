<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application\AppFactory;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaTrait;
use Application\Tags\Taggables\TagCollectionInterface;
use DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method MediaRecord[] getItemsObjects()
 * @property MediaCollection $collection
 */
class MediaFilterCriteria extends DBHelper_BaseFilterCriteria implements TaggableFilterCriteriaInterface
{
    use TaggableFilterCriteriaTrait;

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

    public function getTagCollection(): TagCollectionInterface
    {
        return AppFactory::createMediaCollection();
    }
}
