<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\Collection;

/**
 * Trait used to implement the state-related methods of the
 * {@see RevisionableFilterCriteriaInterface} interface.
 *
 * NOTE: This is used in some applications to create revisionable
 * dependent filters without extending the {@see \Application\Revisionable\Collection\BaseRevisionableFilterCriteria}
 * class.
 *
 * @package Application
 * @subpackage Revisionables
 */
trait RevisionableStateFilterTrait
{
    public function selectState(string $stateName, bool $exclude=false) : self
    {
        $name = RevisionableFilterCriteriaInterface::FILTER_INCLUDE_STATES;
        if($exclude) {
            $name = RevisionableFilterCriteriaInterface::FILTER_EXCLUDE_STATES;
        }

        return $this->selectCriteriaValue($name, $stateName);
    }

    /**
     * @return string[]
     */
    public function getIncludedStates() : array
    {
        return $this->getCriteriaValues(RevisionableFilterCriteriaInterface::FILTER_INCLUDE_STATES);
    }

    /**
     * @return string[]
     */
    public function getExcludedStates() : array
    {
        return $this->getCriteriaValues(RevisionableFilterCriteriaInterface::FILTER_EXCLUDE_STATES);
    }

    public function isStateExcluded(string $state) : bool
    {
        return in_array($state, $this->getExcludedStates());
    }

    public function isStateIncluded(string $state) : bool
    {
        return in_array($state, $this->getIncludedStates());
    }

    protected function applyExcludeStates() : void
    {
        $this->addWhereColumnNOT_IN($this->getStatusColumn(), $this->getExcludedStates());
    }

    protected function applyIncludeStates() : void
    {
        $this->addWhereColumnIN($this->getStatusColumn(), $this->getIncludedStates());
    }
}
