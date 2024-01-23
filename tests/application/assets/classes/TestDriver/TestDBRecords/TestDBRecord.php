<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application\Tags\Taggables\TagContainer;
use Application\Tags\Taggables\TaggableInterface;
use Application\Tags\Taggables\TaggableTrait;
use DBHelper_BaseRecord;

/**
 * @property TestDBCollection $collection
 */
class TestDBRecord extends DBHelper_BaseRecord implements TaggableInterface
{
    use TaggableTrait;

    private array $custom = array();

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(TestDBCollection::COL_LABEL);
    }

    public function getAlias(): string
    {
        return $this->getRecordStringKey(TestDBCollection::COL_ALIAS);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()->link($this->getLabel(), '#');
    }

    public function setLabel(string $label): bool
    {
        return $this->setRecordKey(TestDBCollection::COL_LABEL, $label);
    }

    public function setAlias(string $alias): bool
    {
        return $this->setRecordKey(TestDBCollection::COL_ALIAS, $alias);
    }

    public function setCustomField(string $name, string $value): bool
    {
        if (isset($this->custom[$name]) && $this->custom[$name] === $value) {
            return false;
        }

        $this->custom[$name] = $value;
        $this->setCustomModified($name);
        return true;
    }

    public function getTaggedRecordPrimary(): int
    {
        return $this->getID();
    }

    public function getTagCollection(): TagContainer
    {
        return $this->collection->getTagContainer();
    }
}
