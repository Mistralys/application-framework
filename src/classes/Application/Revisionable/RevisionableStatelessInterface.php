<?php

declare(strict_types=1);

namespace Application\Revisionable;

use Application\Interfaces\ChangelogableInterface;
use Application_CollectionItemInterface;
use Application_EventHandler_EventableListener;
use Application_Interfaces_Disposable;
use Application_Interfaces_Simulatable;
use Application_LockableRecord_Interface;
use Application_RevisionableCollection;
use Application_User;
use DateTime;
use TestDriver\Revisionables\RevisionableCollection;

interface RevisionableStatelessInterface
    extends
    Application_CollectionItemInterface,
    RevisionDependentInterface,
    Application_LockableRecord_Interface,
    Application_Interfaces_Disposable,
    Application_Interfaces_Simulatable,
    RevisionableChangelogInterface
{
    public const ERROR_CANNOT_START_TRANSACTION = 68437001;
    public const ERROR_INVALID_REVISION_STORAGE = 68437002;
    public const ERROR_CANNOT_END_TRANSACTION = 68437003;
    public const ERROR_OPERATION_REQUIRES_TRANSACTION = 68437004;
    public const ERROR_MISSING_PART_SAVE_METHOD = 68437005;
    public const ERROR_STORAGE_PART_ALREADY_REGISTERED = 68437007;
    public const ERROR_DEPENDENT_REVISION_MISMATCH = 68437008;
    public const ERROR_DEPENDENT_CLASS_MISMATCH = 68437009;
    public const ERROR_UNKNOWN_STORAGE_PART = 68437010;
    public const ERROR_TRANSACTION_CHANGELOG_NOT_EMPTY = 68437011;
    public const ERROR_NO_REVISION_SELECTED = 68437012;
    public const ERROR_CANNOT_GET_ADDED_REVISION_DURING_TRANSACTION = 68437013;

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

    public function getRevisionDate() : DateTime;

    /**
     * @return string
     * @deprecated Use {@see self::getRevisionAuthorName()} instead.
     */
    public function getOwnerName() : string;

    /**
     * @return int
     * @deprecated Use {@see self::getRevisionAuthorID()} instead.
     */
    public function getOwnerID() : int;
    public function getRevisionAuthor() : ?Application_User;
    public function getRevisionAuthorName() : string;
    public function getRevisionAuthorID() : int;

    /**
     * Gets the user who created the revisionable (=who created the first revision).
     * @return Application_User
     */
    public function getCreator() : Application_User;

    /**
     * Retrieves the date the revisionable was created (=date of the first revision).
     * @return DateTime
     */
    public function getCreationDate() : DateTime;

    /**
     * Retrieves the date the revisionable was last modified.
     * @return DateTime
     */
    public function getLastModifiedDate() : DateTime;

    /**
     * Like {@see self::getRevision()}, but never returns null.
     * If no revision has been selected or is available, an
     * exception will be thrown.
     *
     * @return int
     * @throws RevisionableException {@see self::ERROR_NO_REVISION_SELECTED}
     */
    public function requireRevision() : int;

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
     * Ensures that a transaction is active for operations that
     * may only be done within a transaction.
     *
     * @param string $developerDetails Optional details for the exception.
     * @throws RevisionableException
     */
    public function requireTransaction(string $developerDetails='') : self;

    /**
     * Whether the last transaction added a new revision.
     * @return bool
     * @see self::getLastAddedRevision()
     */
    public function hasLastTransactionAddedARevision() : bool;

    /**
     * Retrieves the revision number of the revision added by the last transaction, if any.
     * @return int|null
     * @see self::hasLastTransactionAddedARevision()
     */
    public function getLastAddedRevision() : ?int;

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
    public function selectPreviousRevision() : bool;
    /**
     * Selects the very first revision available for the item.
     * @return $this
     */
    public function selectFirstRevision(): self;

    public function getFirstRevision(): int;

    /**
     * Retrieves the previous revision number to the currently selected
     * revision, if any. Returns the number or null if there is none.
     *
     * @return integer|NULL
     */
    public function getPreviousRevision() : ?int;

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

    public function startCurrentUserTransaction(?string $comments = null) : self;

    /**
     * Ends the transaction.
     */
    public function endTransaction(): bool;
    public function rollBackTransaction() : self;
    public function isTransactionStarted() : bool;

    /**
     * Returns the pretty revision number as relevant for humans.
     * @return int
     */
    public function getPrettyRevision(): int;

    /**
     * Saves the item using whatever storage the item uses.
     */
    public function save(): bool;

    public function hasChanges() : bool;

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
     * Reloads the revisionable if it has been disposed,
     * and returns a fresh instance. Otherwise, the current
     * instance is returned.
     *
     * @return RevisionableStatelessInterface
     */
    public function reload() : RevisionableStatelessInterface;

    /**
     * Retrieves key => value pairs of all non-standard revision fields
     * that must be stored in the revision history.
     *
     * In practice, this means all fields beyond the automatically
     * handled ones like {@see RevisionableCollection::COL_REV_AUTHOR}
     * or {@see RevisionableCollection::COL_REV_COMMENTS}.
     *
     * @return array<string, mixed>
     */
    public function getCustomKeyValues(): array;

    public function handle_revisionLoaded(int $number): void;

    /**
     * Gets an instance of the revisionable's collection instance.
     * @return Application_RevisionableCollection
     */
    public function getCollection() : Application_RevisionableCollection;


    /**
     * Adds a callback for when a revisionable change transaction has ended.
     *
     * The callback gets a single parameter:
     *
     * 1. The event object {@see TransactionEndedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onTransactionEnded(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * Adds a callback for when a new revision is added to the revisionable.
     *
     * The callback gets a single parameter:
     *
     * 1. The event object {@see RevisionAddedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     * @see RevisionAddedEvent
     */
    public function onRevisionAdded(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * Adds a callback to call before the revisionable is saved.
     *
     * This gets a single parameter:
     *
     * - The event object {@see \Application\Revisionable\Event\BeforeSaveEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onBeforeSave(callable $callback) : Application_EventHandler_EventableListener;

    /**
     * Adds a callback to whenever a different revisionable revision
     * has been selected.
     *
     * This gets a single parameter:
     *
     * - The event object {@see \Application\Revisionable\Event\RevisionSelectedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onRevisionSelected(callable $callback) : Application_EventHandler_EventableListener;
}
