<?php

class Application_Changelog
{
    const ERROR_MISSING_CHANGELOG_KEY = 599601;
    
    const ERROR_UNKNOWN_CHANGELOG_ENTRY = 599602;
    
   /**
    * @var Application_Changelogable_Interface
    */
    protected $owner;
    
    public function __construct(Application_Changelogable_Interface $owner)
    {
        $this->owner = $owner;
    }
    
   /**
    * Retrieves all existing changelog entries.
    * @return Application_Changelog_Entry[]
    */
    public function getEntries()
    {
        $filters = $this->getFilters();
        return $filters->getItemsObjects();
    }
    
    public function countEntries()
    {
        $filters = $this->getFilters();
        return $filters->countItems();
    }
    
   /**
    * Retrieves an existing changelog entry by its ID.
    * 
    * @param integer $id
    * @return Application_Changelog_Entry
    */
    public function getByID($id)
    {
        require_once 'Application/Changelog/Entry.php';
        
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
        
        return new Application_Changelog_Entry(
            $this->owner,
            $entry['changelog_id'],
            $entry['changelog_author'],
            $entry['changelog_type'],
            $entry['changelog_date'],
            $entry['changelog_data']
        );
    }
    
   /**
    * Commits the current changelog queue of the owner
    * of this changelog, by inserting all entries into
    * the according changelog table.
    */
    public function commitQueue()
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
    }
    
   /**
    * Commits a single changelog entry. The data is automatically serialized
    * using <code>json_encode</code>.
    * 
    * @param string $type
    * @param mixed $data
    * @return string
    */
    protected function commitQueueEntry($type, $data)
    {
        $this->log(sprintf('Committing entry of type [%s].', $type));
        
        $primary = $this->owner->getChangelogItemPrimary();
        $record = $primary;
        $record['changelog_author'] = Application::getUser()->getID();
        $record['changelog_type'] = $type;
        $record['changelog_date'] = date('Y-m-d H:i:s');
        $record['changelog_data'] = json_encode($data);
        
        $fields = array();
        $placeholders = array();
        
        foreach($record as $varName => $value) {
            $fields[] = sprintf(
                "`%s`=:%s",
                $varName,
                $varName
            );
            
            $placeholders[':'.$varName] = $value; 
        }
        
        $statement = 
        "INSERT INTO
                `".$this->owner->getChangelogTable()."`
        SET 
            ".implode(", ", $fields);
        
        return DBHelper::insert($statement, $placeholders);
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
    
    public function getTableName()
    {
        return $this->owner->getChangelogTable();
    }
    
    protected $cachedFilters;
    
    public function getFilters()
    {
        if(!isset($this->cachedFilters)) {
            Application::requireClass('Application_Changelog_FilterCriteria');
            $this->cachedFilters = new Application_Changelog_FilterCriteria($this);
        }
        
        return $this->cachedFilters;
    }
    
    public function getOwner()
    {
        return $this->owner;
    }
    
    public function getPrimary()
    {
        return $this->owner->getChangelogItemPrimary();
    }
    
   /**
    * Retrieves all authors that have contributed to the item's
    * changelog in the current revision.
    * 
    * @return Application_User[]
    */
    public function getAuthors()
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
        
        $user_ids = DBHelper::fetchAllKey('changelog_author', $query, $placeholders);
        $users = array();
        foreach($user_ids as $user_id) {
            $user = Application::getUser()->createByID($user_id);
            if($user instanceof Application_User) {
                $users[] = $user;
            }
        }
        
        return $users;
    }
    
    public function getTypes()
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
    
    public function getTypeLabel($type)
    {
        return $this->owner->getChangelogTypeLabel($type);
    }
}

