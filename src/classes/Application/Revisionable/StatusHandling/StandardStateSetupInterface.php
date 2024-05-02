<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\StatusHandling;

use Application\Revisionable\RevisionableInterface;
use DateTime;

/**
 * For details, see {@see StandardStateSetupTrait}.
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see StandardStateSetupTrait
 */
interface StandardStateSetupInterface extends RevisionableInterface
{
    public const ERROR_NO_FINALIZED_REVISION = 153101;

    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DELETED = 'deleted';

    /**
     * Sets the state of the record to "Finalized" in a transaction, and
     * saves the state change.
     *
     * NOTE: This method is made to be used standalone,
     * outside a transaction since it uses its own transaction.
     *
     * @param string|NULL $comments
     * @return $this
     */
    public function makeFinalized(?string $comments=null) : self;

    /**
     * Sets the state of the record to "Inactive" in a transaction, and
     * saves the state change.
     *
     * NOTE: This method is made to be used standalone,
     * outside a transaction since it uses its own transaction.
     *
     * @param string|NULL $comments
     * @return $this
     */
    public function makeInactive(?string $comments=null) : self;

    /**
     * Sets the state of the record to "Deleted" in a transaction, and
     * saves the state change.
     *
     * NOTE: This method is made to be used standalone,
     * outside a transaction since it uses its own transaction.
     *
     * @param string|NULL $comments
     * @return $this
     */
    public function makeDeleted(?string $comments=null) : self;

    /**
     * Sets the state of the record to "Draft" in a transaction, and
     * saves the state change.
     *
     * NOTE: This method is made to be used standalone,
     * outside a transaction since it uses its own transaction.
     *
     * @param string|NULL $comments
     * @return $this
     */
    public function makeDraft(?string $comments=null) : self;

    public function isFinalized() : bool;
    public function isInactive() : bool;
    public function isDeleted() : bool;
    public function isDraft() : bool;

    public function canBeMadeInactive() : bool;
    public function canBeDeleted() : bool;
    public function canBeFinalized() : bool;
    public function canBeDestroyed() : bool;

    public function getLatestFinalizedRevision() : ?int;
    public function hasFinalizedRevision() : bool;
    public function selectLatestFinalizedRevision() : self;
    public function getDateLastFinalized() : ?DateTime;

    /**
     * Gets the pretty revision number of the latest finalized revision,
     * or the current pretty revision otherwise.
     *
     * @return int
     */
    public function getFinalizedPrettyRevision() : int;
}
