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

    public const string FILTER_EXTENSIONS = 'extensions';
    public const string FILTER_TYPES = 'types';

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

    /**
     * @param string[] $types Media type IDs (e.g. `Image`, `PDF`).
     * @return $this
     */
    public function selectMediaTypes(array $types) : self
    {
        foreach($types as $type) {
            $this->selectMediaType($type);
        }

        return $this;
    }

    /**
     * @param string $type A media type ID (e.g. `Image`, `PDF`).
     * @return $this
     */
    public function selectMediaType(string $type) : self
    {
        return $this->selectCriteriaValue(self::FILTER_TYPES, $type);
    }

    protected function getCountColumn(): string
    {
        return $this->getColPrimary();
    }

    public function getColPrimary() : string
    {
        return $this->resolveTableSelector().'.'.MediaCollection::PRIMARY_NAME;
    }

    protected function prepareQuery(): void
    {
        $this->addWhereColumnIN(MediaCollection::COL_EXTENSION, $this->getCriteriaValues(self::FILTER_EXTENSIONS));
        $this->addWhereColumnIN(MediaCollection::COL_TYPE, $this->getCriteriaValues(self::FILTER_TYPES));
    }

    /**
     * Appends the primary key as a tie-breaker to the order direction,
     * unless the order field already is the primary key. This keeps paging
     * stable, duplicate-free and gap-free when several records share the
     * same value for the chosen order field (e.g. several documents added
     * within the same second).
     *
     * @return string
     */
    protected function buildOrderby() : string
    {
        $sql = parent::buildOrderby();

        if($sql === '' || $this->getOrderField() === MediaCollection::PRIMARY_NAME) {
            return $sql;
        }

        return $sql.', '.$this->getColPrimary().' '.$this->getOrderDir();
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
