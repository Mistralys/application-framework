<?php

declare(strict_types=1);

use Application\Application;
use Application\Changelog\Event\ChangelogCommittedEvent;
use Application\Interfaces\ChangelogableInterface;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\ConvertHelper_Exception;

class Application_Changelog implements Application_Interfaces_Eventable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public const int ERROR_MISSING_CHANGELOG_KEY = 599601;
    public const int ERROR_UNKNOWN_CHANGELOG_ENTRY = 599602;
    public const string COL_AUTHOR = 'changelog_author';
    public const string COL_TYPE = 'changelog_type';
    public const string COL_DATE = 'changelog_date';
    public const string COL_DATA = 'changelog_data';
    public const string COL_PRIMARY_ID = 'changelog_id';

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
                `changelog_id`=:primary",
            array(
                'primary' => $id
            )    
        );
        
        if(!is_array($entry) || !isset($entry[self::COL_PRIMARY_ID])) {
            throw new Application_Exception(
                'Unknown changelog entry',
                sprintf(
                    'Could not find the changelog entry [%s] in the database.',
                    $id        
                ),
                self::ERROR_UNKNOWN_CHANGELOG_ENTRY
            );
        }
        
        return new Application_Changelog_Entry(
            $this->owner,
            (int)$entry[self::COL_PRIMARY_ID],
            (int)$entry[self::COL_AUTHOR],
            (string)$entry[self::COL_TYPE],
            (string)$entry[self::COL_DATE],
            (string)$entry[self::COL_DATA],
            $entry
        );
    }

   /**
    * Commits the current changelog queue of this
    * changelog's owner, by inserting all entries into
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
        
        $record = $this->getInsertColumns();
        $record[self::COL_AUTHOR] = Application::getUser()->getID();
        $record[self::COL_TYPE] = $type;
        $record[self::COL_DATE] = date('Y-m-d H:i:s');
        $record[self::COL_DATA] = JSONConverter::var2json($data);
        
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
        if(!isset($this->cachedFilters))
        {
            $this->cachedFilters = new Application_Changelog_FilterCriteria($this);
        }
        
        return $this->cachedFilters;
    }
    
    public function getOwner() : ChangelogableInterface
    {
        return $this->owner;
    }

    /**
     * Gets all fields required to uniquely identify a single changelog entry.
     * @return array<string,string|int>
     */
    public function getPrimary() : array
    {
        return $this->owner->getChangelogItemPrimary();
    }

    /**
     * @return array<string,string|int>
     */
    public function getInsertColumns() : array
    {
        return $this->owner->getChangelogItemInsertColumns();
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
        // fetch a list of items. For single items, the primary key
        // is enough as primary.
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
        
        $user_ids = DBHelper::fetchAllKeyInt(self::COL_AUTHOR, $query, $placeholders);
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
        // is enough as primary.
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
        $types = DBHelper::fetchAllKey(self::COL_TYPE, $query, $placeholders);
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

