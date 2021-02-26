<?php 

interface Application_Revisionable_Interface
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
     * Retrieves an identification of the object in string form,
     * with typically the ID and human readable label of the item.
     * This is used for example in log messages to help identify
     * the source. It is not used to display to the user.
     *
     * @return string
     */
    public function getIdentification();

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
    * Retrieves a human readable label of the item, typcially only
    * used in the administration to recognize it by.
    * 
    * @return string
    */
    public function getLabel() : string;
    
   /**
    * Disposes of the item's internal storage: in lieu of unloading
    * the item (which is almost impossible due to all the object 
    * references), the internal data is cleared and freed up to liberate
    * memory, but so that revision data can be loaded again if needed.
    * 
    * Of course this should be done when it is reasonable certain the 
    * item will not be needed anymore, to avoid loading the same data
    * several times.
    */
    public function dispose();
    
   /**
    * Whether the revisionable is editable, i.e. its properties
    * and content may be modified. This usually ties into the 
    * lock manager as well, if the item supports locking.
    * 
    * @return bool
    */
    public function isEditable();
    
   /**
    * Whether the item can be locked in specific adminstration
    * screens with the the lock manager.
    * 
    * @return bool
    */
    public function isLockable();
    
   /**
    * Sets the lock manager instance used to handle locking
    * of this revisionable item. This is set automatically by
    * the administration areas if the item supports locking.
    * 
    * @param Application_LockManager $lockManager
    */
    public function setLockManager(Application_LockManager $lockManager);
    
   /**
    * Alias for querying the lock manager for this item.
    * Will return true if the item supports locking, and is
    * locked in the current administration area.
    */
    public function isLocked();
    
   /**
    * Sets whether this revisionable item can be exported.
    * 
    * @return boolean
    */
    public function isExportable();
    
   /**
    * Returns the item's version, if exportable, that should be
    * exported when used in an export. 
    * 
    * @return integer|NULL Returns NULL if no version has been selected.
    */
    public function getExportRevision();
    
   /**
    * Retrieves the revisionable's lock manager, if any has been set.
    * @return Application_LockManager|NULL
    */
    public function getLockManager();
}

