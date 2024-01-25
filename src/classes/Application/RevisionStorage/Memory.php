<?php
/**
 * File containing the {@link Application_RevisionStorage_Memory} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionStorage_Memory
 */

use Application\Revisionable\RevisionableException;

/**
 * Utility class for storing revision data: stores data sets
 * by revision number, and allows selecting revisions / switching
 * between revisions to retrieve revision-specific data.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_RevisionStorage_Memory extends Application_RevisionStorage
{
    public function getTypeID() : string
    {
        return 'Memory';
    }
    
    public const ERROR_REVISION_DOES_NOT_EXIST = 117756001;
    
    public const ERROR_COPYTO_NOT_IMPLEMENTED = 117756002;
    
    public const ERROR_FILTER_CRITERIA_NOT_AVAILABLE = 117756003;

    protected function _loadRevision(int $number) : void
    {
        // no data to load
    }

    public function countRevisions() : int
    {
        return count($this->data);
    }

    public function revisionExists(int $number, bool $forceLiveCheck=false) : bool
    {
        return isset($this->data[$number]);
    }


    /**
     * Copies the data from the source revision to the
     * target revision number. Both revisions have to
     * exist, otherwise this will trigger an exception.
     *
     * All reference types get cloned to avoid reference
     * issues in the data keys.
     *
     * @param int $sourceRevision
     * @param int $targetRevision
     * @throws InvalidArgumentException
     */
    public function copy($sourceRevision, $targetRevision, $targetOwnerID, $targetOwnerName, $targetComments, DateTime $targetDate=null)
    {
        if (!$this->revisionExists($sourceRevision) || !$this->revisionExists($targetRevision)) {
            throw new InvalidArgumentException('The source or target revisions do not exist.');
        }
        
        if(!$targetDate) { 
            $targetDate = new DateTime(); 
        }

        $copiedData = array();
        foreach ($this->data[$sourceRevision] as $keyName => $value) {
            $copiedData[$keyName] = $this->recursiveClone($value);
        }

        // overwrite these keys as they are the ones that are
        // unique to each revision
        $copiedData['__timestamp'] = $targetDate->getTimestamp();
        $copiedData['__ownerID'] = $targetOwnerID;
        $copiedData['__ownerName'] = $targetOwnerName;
        $copiedData['__comments'] = $targetComments;

        $this->data[$targetRevision] = $copiedData;
    }

    /**
     * Removes a revision. Note that only the latest
     * revision may be removed. If you wish to remove
     * an earlier revision, you will need to remove all
     * revisions that came after it.
     *
     * @return $this
     */
    protected function _removeRevision(int $number) : self
    {
        unset($this->data[$number]);
        return $this;
    }

    /**
     * Takes any type of variable and makes sure that reference
     * types get cloned. Returns the variable with cloned contents
     * if needed. Recurses into arrays.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function recursiveClone($value)
    {
        if (is_array($value)) {
            foreach ($value as $idx => $item) {
                $value[$idx] = $this->recursiveClone($item);
            }

            return $value;
        }

        if (is_object($value)) {
            $newObj = clone $value;

            return $newObj;
        }

        return $value;
    }

    public function nextRevision() : int
    {
        return $this->getLatestRevision() + 1;
    }

    public function getRevisions() : array
    {
        $revisions = array_keys($this->data);
        sort($revisions);

        return $revisions;
    }
    
    public function getFilterCriteria() : Application_FilterCriteria_RevisionableRevisions
    {
        throw new RevisionableException(
            'Not implemented',
            'Filter criteria are not available for memory revision storage.',
            self::ERROR_FILTER_CRITERIA_NOT_AVAILABLE
        );
    }
    
    public function copyTo(Application_Revisionable $revisionable) : void
    {
        throw new RevisionableException(
            'Not implemented',
            'Copying between revisions in memory is not supported.',
            self::ERROR_COPYTO_NOT_IMPLEMENTED        
        );
    }
    
    public function _hasRevdata() : bool
    {
        return false;
    }
    
    protected function _loadRevdataKey(string $name) : string
    {
        return '';
    }
    
    protected function _writeRevdataKey(string $key, $value) : void
    {
        
    }
}
