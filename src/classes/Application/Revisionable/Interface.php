<?php 

declare(strict_types=1);

interface Application_Revisionable_Interface
    extends
    Application_LockableRecord_Interface,
    Application_Interfaces_Disposable
{
   /**
    * Locks the currently selected revision, so that any
    * calls to {@link selectRevision()} will not be honored.
    * 
    * @see unlockRevision()
    */
    public function lockRevision() : self;
    
   /**
    * Unlocks the revision selection after a call to {@link lockRevision()}.
    * 
    * @see lockRevision()
    */
    public function unlockRevision() : self;
    
   /**
    * Checks whether selecting revisions is currently locked.
    * 
    * @return boolean
    */
    public function isRevisionLocked() : bool;
    
    /**
     * The number of revisions in the item's version history.
     * @return int
     */
    public function countRevisions() : int;

    /**
     * Gets the comments for the active revision (the reason for the new
     * revision, optional information). Returns null if none has
     * been provided.
     *
     * @return string|NULL
     */
    public function getRevisionComments() : ?string;

    /**
     * Retrieves an indexed array with revision numbers in the
     * order they were added, from earliest to latest.
     *
     * @return int[]
     */
    public function getRevisions() : array;

    /**
     * Selects a specific revision of the item to work with.
     * @param int $number
     */
    public function selectRevision(int $number) : self;

    /**
     * Checks if the specified revision number exists for the item.
     * @param int $number
     */
    public function revisionExists(int $number) : bool;

    public function getRevisionTimestamp();

    /**
     * Remembers the current revision number, so it can be restored
     * later using the {@link restoreRevision()} method.
     *
     * @see restoreRevision()
     */
    public function rememberRevision();

    /**
     * Restores the revision previously selected using
     * the {@link rememberRevision()} method.
     *
     * @see rememberRevision();
     */
    public function restoreRevision();

    /**
     * Starts a modification transaction: does all modifications
     * in a new revision, and only commits the changes if all
     * goes well (and if a new revision is required, since some
     * changes often do not require a new revision).
     *
     * @see endTransaction()
     * @return $this
     */
    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null) : self;

    /**
     * Ends the transaction.
     */
    public function endTransaction() : bool;

    public function setState(Application_StateHandler_State $newState) : self;

    /**
     * Returns the currently selected revision number of the item.
     * @return int
     */
    public function getRevision() : int;
    
   /**
    * Returns the pretty revision number as relevant for humans.
    * @return int
    */
    public function getPrettyRevision() : int;

    /**
     * Saves the item using whatever storage the item uses.
     */
    public function save();

   /**
    * Retrieves the item's primary ID.
    * @return int
    */
    public function getID() : int;
    
    /**
     * Retrieves the latest revision number available for the item.
     * @return int
     */
    public function getLatestRevision() : int;
    
   /**
    * Retrieves the type name of the revisionable, as help to
    * identify revisionable types in logs and the like. By default,
    * this is the last part of the class name.
    * 
    * @return string
    */
    public function getRevisionableTypeName() : string;
}
