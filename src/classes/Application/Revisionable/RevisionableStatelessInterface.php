<?php

declare(strict_types=1);

namespace Application\Revisionable;

use Application\Interfaces\ChangelogableInterface;
use Application_Interfaces_Disposable;
use Application_Interfaces_Simulatable;
use Application_LockableRecord_Interface;

interface RevisionableStatelessInterface
    extends
    Application_LockableRecord_Interface,
    Application_Interfaces_Disposable,
    Application_Interfaces_Simulatable,
    ChangelogableInterface
{
    /**
     * Locks the currently selected revision, so that any
     * calls to {@see self::selectRevision()} will not be honored.
     *
     * @see self::unlockRevision()
     */
    public function lockRevision(): self;

    /**
     * Unlocks the revision selection after a call to {@see self::lockRevision()}.
     *
     * @see self::lockRevision()
     */
    public function unlockRevision(): self;

    /**
     * Checks whether selecting revisions is currently locked.
     *
     * @return boolean
     */
    public function isRevisionLocked(): bool;

    /**
     * The number of revisions in the item's version history.
     * @return int
     */
    public function countRevisions(): int;

    /**
     * Gets the comments for the active revision (the reason for the new
     * revision, optional information). Returns null if none has
     * been provided.
     *
     * @return string|NULL
     */
    public function getRevisionComments(): ?string;

    /**
     * Retrieves an indexed array with revision numbers in the
     * order they were added, from earliest to latest.
     *
     * @return int[] Can be empty.
     */
    public function getRevisions(): array;

    /**
     * Selects a specific revision of the item to work with.
     * @param int $number
     */
    public function selectRevision(int $number): self;

    /**
     * Checks if the specified revision number exists for the item.
     * @param int $number
     */
    public function revisionExists(int $number): bool;

    /**
     * @return int|null Can return NULL if no revision is selected or available.
     */
    public function getRevisionTimestamp(): ?int;

    /**
     * Remembers the current revision number, so it can be restored
     * later using the {@see self::restoreRevision()} method.
     *
     * @see self::restoreRevision()
     */
    public function rememberRevision(): self;

    /**
     * Restores the revision previously selected using
     * the {@see self::rememberRevision()} method.
     *
     * @return $this
     * @see self::rememberRevision();
     */
    public function restoreRevision(): self;

    /**
     * Selects the most recent revision of the item.
     * @return $this
     */
    public function selectLatestRevision(): self;

    /**
     * Selects the very first revision available for the item.
     * @return $this
     */
    public function selectFirstRevision(): self;

    public function getFirstRevision(): int;

    /**
     * Starts a modification transaction: does all modifications
     * in a new revision, and only commits the changes if all
     * goes well (and if a new revision is required, since some
     * changes often do not require a new revision).
     *
     * @return $this
     * @see endTransaction()
     */
    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null): self;

    /**
     * Ends the transaction.
     */
    public function endTransaction(): bool;

    /**
     * Returns the currently selected revision number of the item.
     * @return int
     */
    public function getRevision(): int;

    /**
     * Returns the pretty revision number as relevant for humans.
     * @return int
     */
    public function getPrettyRevision(): int;

    /**
     * Saves the item using whatever storage the item uses.
     */
    public function save(): bool;

    /**
     * Retrieves the item's primary ID.
     * @return int
     */
    public function getID(): int;

    /**
     * Retrieves the latest revision number available for the item.
     * @return int
     */
    public function getLatestRevision(): int;

    /**
     * Retrieves the type name of the revisionable, as help to
     * identify revisionable types in logs and the like. By default,
     * this is the last part of the class name.
     *
     * @return string
     */
    public function getRevisionableTypeName(): string;

    /**
     * @return array<string, mixed>
     */
    public function getCustomKeyValues(): array;

    public function handle_revisionLoaded(int $number): void;
}