<?php

declare(strict_types=1);

use Application\Changelog\Event\ChangelogCommittedEvent;
use Application\Interfaces\ChangelogableInterface;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\ConvertHelper_Exception;

class Application_Changelog implements Application_Interfaces_Eventable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public const ERROR_MISSING_CHANGELOG_KEY = 599601;
    public const ERROR_UNKNOWN_CHANGELOG_ENTRY = 599602;
    
    protected ChangelogableInterface $owner;
    private string $logIdentifier;

    public function __construct(ChangelogableInterface $owner)
    {
        $this->owner = $owner;
        $this->logIdentifier = getClassTypeName($owner).' | ChangeLog';
    }
    
   /**
    * Retrieves all existing changelog entries.
    * @return Application_Changelog_Entry[]
    */
    public function getEntries() : array
    {
        return $this->getFilters()->getItemsObjects();
    }
    
    public function countEntries() : int
    {
        return $this->getFilters()->countItems();
    }
    
   /**
    * Retrieves an existing changelog entry by its ID.
    * 
    * @param integer $id
    * @return Application_Changelog_Entry
    */
    public function getByID(int $id) : Application_Changelog_Entry
    {
        $entry = DBHelper::fetch(
            "SELECT
                *
            FROM
                `".$this->getTableName()."`
            WHERE
                `changelog_id`=:changelog_id",
            array(
                'changelog_id' => $id
            )    
        );
        
        if(!is_array($entry) || !isset($entry['changelog_id'])) {
            throw new Application_Exception(
                'Unknown changelog entry',
                sprintf(
                    'Could not find the changelog entry [%s] in the database.',
                    $id        
                ),
                self::ERROR_UNKNOWN_CHANGELOG_ENTRY
            );
        }
        
        return $this->createEntry(
            $this->owner,
            (int)$entry['changelog_id'],
            (int)$entry['changelog_author'],
            (string)$entry['changelog_type'],
            (string)$entry['changelog_date'],
            (string)$entry['changelog_data'],
            $entry
        );
    }

    protected function createEntry(
        ChangelogableInterface $owner,
        int $changelogID,
        int $authorID,
        string $type,
        string $date,
        string $data,
        array $entry
    ) : Application_Changelog_Entry
    {
        return new Application_Changelog_Entry(
            $owner,
            $changelogID,
            $authorID,
            $type,
            $date,
            $data,
            $entry
        );
    }

   /**
    * Commits the current changelog queue of the owner
    * of this changelog, by inserting all entries into
    * the according changelog table.
    */
    public function commitQueue() : void
    {
        $entries = $this->owner->getChangelogQueue();
        if(empty($entries)) {
            $this->log('No changelog entries found.');
            return;
        }
        
        $this->log(sprintf('Committing [%s] changelog entries.', count($entries)));
        
        foreach($entries as $entry) {
            if(!isset($entry['type']) || !array_key_exists('data', $entry)) {
                throw new Application_Exception(
                    'Missing changelog key',
                    sprintf(
                        'The changelog entry is missing one or both of the [%s] or [%s] keys.',
                        'type',
                        'data'    
                    ),
                    self::ERROR_MISSING_CHANGELOG_KEY
                );
            }
            
            $this->commitQueueEntry($entry['type'], $entry['data']);
        }

        $this->triggerQueueCommitted();
    }

    private function triggerQueueCommitted() : void
    {
        $this->triggerEvent(
            ChangelogCommittedEvent::EVENT_NAME,
            array(),
            ChangelogCommittedEvent::class
        );
    }

    public function onQueueCommitted(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(ChangelogCommittedEvent::EVENT_NAME, $callback);
    }

    /**
     * Commits a single changelog entry.
     * The data is automatically serialized to JSON.
     *
     * @param string $type
     * @param mixed $data
     * @return string
     * @throws Application_Exception
     * @throws JSONConverterException
     */
    protected function commitQueueEntry(string $type, $data) : string
    {
        $this->log(sprintf('Committing entry of type [%s].', $type));
        
        $record = $this->getPrimary();
        $record['changelog_author'] = Application::getUser()->getID();
        $record['changelog_type'] = $type;
        $record['changelog_date'] = date('Y-m-d H:i:s');
        $record['changelog_data'] = JSONConverter::var2json($data);
        
        return DBHelper::insertDynamic(
            $this->owner->getChangelogTable(),
            $record
        );
    }
    
   /**
    * Logs a message for the changelog into the application log.
    * @param string $message
    */
    protected function log($message)
    {
        Application::log(sprintf(
            'Changelog | %s | %s',
            $this->owner->getChangelogTable(),
            $message
        ));
    }
    
    public function getTableName() : string
    {
        return $this->owner->getChangelogTable();
    }
    
    protected ?Application_Changelog_FilterCriteria $cachedFilters = null;
    
    public function getFilters() : Application_Changelog_FilterCriteria
    {
        if(!isset($this->cachedFilters)) {
            $this->cachedFilters = $this->createFilters();
        }
        
        return $this->cachedFilters;
    }

    protected function createFilters() : Application_Changelog_FilterCriteria
    {
        return new Application_Changelog_FilterCriteria($this);
    }

    public function getOwner() : ChangelogableInterface
    {
        return $this->owner;
    }

    /**
     * Gets all fields required to uniquely identify a single changelog entry.
     * @return array<string,string>
     */
    public function getPrimary() : array
    {
        return $this->owner->getChangelogItemPrimary();
    }

    /**
     * Gets all fields required to select all changelog entries for the owner.
     * @return array<string,string>
     */
    public function getFilterSelects() : array
    {
        return $this->owner->getChangelogFilterSelects();
    }
    
   /**
    * Retrieves all authors that have contributed to the item's
    * changelog in the current revision.
    * 
    * @return Application_User[]
    */
    public function getAuthors() : array
    {
        $query =
        "SELECT
            changelog_author
        FROM
           `".$this->getTableName()."`";
        
        $where = array();
        $placeholders = array();
        
        // only use the primary values if set, and if the query is to
        // fetch a list of items. For single items, the changelog_id
        // is sufficient as primary.
        $primary = $this->getPrimary();
        if(!empty($primary)) {
            foreach($primary as $key => $val) {
                $where[] = "`".$key."`=:".$key;
                $placeholders[':'.$key] = $val;
            }
        }
        
        if(!empty($where)) {
            $query .= " WHERE ".implode(" AND ", $where);
        }
        
        $query .= " GROUP BY changelog_author";
        
        $user_ids = DBHelper::fetchAllKeyInt('changelog_author', $query, $placeholders);
        $users = array();

        foreach($user_ids as $user_id) {
            if(Application::userIDExists($user_id)) {
                $users[] = Application::createUser($user_id);
            }
        }
        
        return $users;
    }

    /**
     * @return array<string,string> Type ID => Human-readable label pairs.
     * @throws DBHelper_Exception
     * @throws ConvertHelper_Exception
     */
    public function getTypes() : array
    {
        $query =
        "SELECT
            changelog_type
        FROM
           `".$this->getTableName()."`";
        
        $where = array();
        $placeholders = array();
        
        // only use the primary values if set, and if the query is to
        // fetch a list of items. For single items, the changelog_id
        // is sufficient as primary.
        $primary = $this->getPrimary();
        if(!empty($primary)) {
            foreach($primary as $key => $val) {
                $where[] = "`".$key."`=:".$key;
                $placeholders[':'.$key] = $val;
            }
        }
        
        if(!empty($where)) {
            $query .= " WHERE ".implode(" AND ", $where);
        }
        
        $query .= " GROUP BY changelog_type";
        
        $items = array();
        $types = DBHelper::fetchAllKey('changelog_type', $query, $placeholders);
        foreach($types as $type) {
            $items[$type] = $this->owner->getChangelogTypeLabel($type);
        }
        
        asort($items);
        
        return $items;
    }
    
    public function getTypeLabel(string $type) : string
    {
        return $this->owner->getChangelogTypeLabel($type);
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }
}

