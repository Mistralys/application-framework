<?php 

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
    public function lockRevision();
    
   /**
    * Unlocks the revision selection after a call to {@link lockRevision()}.
    * 
    * @see lockRevision()
    */
    public function unlockRevision();
    
   /**
    * Checks whether selecting revisions is currently locked.
    * 
    * @return boolean
    */
    public function isRevisionLocked();
    
    /**
     * The amount of revisions in the item's version history.
     * @return int
     */
    public function countRevisions();

    /**
     * Gets the comments for the active revision (the reason for the new
     * revision, an optional information). Returns null if none has
     * been provided.
     *
     * @return string|NULL
     */
    public function getRevisionComments();

    /**
     * Retrieves an indexed array with revision numbers in the
     * order they were added, from earliest to latest.
     *
     * @return array
     */
    public function getRevisions();

    /**
     * Selects a specific revision of the item to work with.
     * @param int $number
     */
    public function selectRevision($number);

    /**
     * Checks if the specified revision number exists for the item.
     * @param int $number
     */
    public function revisionExists($number);

    public function getRevisionTimestamp();

    /**
     * Remembers the current revision number so it can be restored
     * later using the {@link restoreRevision()} method.
     *
     * @see restoreRevision()
     */
    public function rememberRevision();

    /**
     * Restores the revision that was previously selected using
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
     */
    public function startTransaction($ownerID, $ownerName, $comments = '');

    /**
     * Ends the transaction.
     */
    public function endTransaction();

    /**
     * Returns the currently selected revision number of the item.
     * @return int
     */
    public function getRevision();
    
   /**
    * Returns the pretty revision number as relevant for humans.
    * @return int
    */
    public function getPrettyRevision();

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
    public function getLatestRevision();
    
   /**
    * Retrieves the type name of the revisionable, as help to
    * identify revisionable types in logs and the like. By default
    * this is the last part of the class name.
    * 
    * @return string
    */
    public function getRevisionableTypeName();
    
   /**
    * Sets whether this revisionable item can be exported.
    * 
    * @return boolean
    */
    public function isExportable() : bool;
    
   /**
    * Returns the item's version, if exportable, that should be
    * exported when used in an export. 
    * 
    * @return integer|NULL Returns NULL if no version has been selected.
    */
    public function getExportRevision() : ?int;
}

