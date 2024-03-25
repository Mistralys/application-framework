<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\RevisionableCollection;

use Application\Interfaces\FilterCriteriaInterface;
use Application\Revisionable\RevisionableInterface;

/**
 * Interface for filter criteria that support revisionable objects,
 * with methods to filter by record status.
 *
 * @package Application
 * @subpackage Revisionables
 */
interface RevisionableFilterCriteriaInterface extends FilterCriteriaInterface
{
    public const FILTER_INCLUDE_STATES = 'include_state';
    public const FILTER_EXCLUDE_STATES = 'exclude_state';

    /**
     * The name of the column in which the revision number is stored.
     * @return string
     */
    public function getRevisionColumn() : string;

    public function getStatusColumn() : string;

    /**
     * @return RevisionableInterface[]
     */
    public function getItemsObjects() : array;

    /**
     * Retrieves all revisionable revisions for the current filters.
     * These revisions are always the current revisions for the records.
     *
     * @return integer[]
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
