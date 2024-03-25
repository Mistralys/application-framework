<?php

declare(strict_types=1);

namespace Application\RevisionableCollection;

use Application\Interfaces\FilterCriteriaInterface;
use Application_RevisionableCollection_DBRevisionable;

interface RevisionableFilterCriteriaInterface extends FilterCriteriaInterface
{
    /**
     * @return Application_RevisionableCollection_DBRevisionable[]
     */
    public function getItemsObjects() : array;

    public function getIDs() : array;

    /**
     * @return int[]
     */
    public function getRevisions() : array;

    /**
     * Selects only lists with or without the specified state.
     *
     * @param string $stateName
     * @param boolean $exclude Whether to exclude this state. Defaults to including it.
     * @return $this
     */
    public function selectState(string $stateName, bool $exclude=false) : self;

    /**
     * @return string[]
     */
    public function getIncludedStates() : array;

    /**
     * @return string[]
     */
    public function getExcludedStates() : array;

    public function isStateExcluded(string $state) : bool;

    public function isStateIncluded(string $state) : bool;
}
