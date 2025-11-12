<?php
/**
 * File containing the {@see Application_Messagelogs} class.
 * 
 * @package Application
 * @subpackage Logging
 * @see Application_Messagelogs
 */

use Application\AppFactory;

/**
 * Used to add and retrieve messages stored stored in the
 * database, in the `app_messagelog` table. This is intended
 * to be used to messages that need to be persisted.
 * 
 * The messages can be viewed in the UI.
 * 
 * @package Application
 * @subpackage Logging
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method Application_Messagelogs_FilterCriteria getFilterCriteria()
 * @method Application_Messagelogs_Log getByID($log_id)
 * @method Application_Messagelogs_FilterSettings getFilterSettings()
 * @method Application_Messagelogs_Log createNewRecord(array $data=array(), bool $silent=false)
 */
class Application_Messagelogs extends DBHelper_BaseCollection
{
    public const MESSAGELOG_INFORMATION = 'info';
    public const MESSAGELOG_ERROR = 'error';
    public const MESSAGELOG_WARNING = 'warning';
    public const MESSAGE_TYPES = array(
        self::MESSAGELOG_INFORMATION,
        self::MESSAGELOG_ERROR,
        self::MESSAGELOG_WARNING
    );
    public const COL_MESSAGE = 'message';
    public const COL_CATEGORY = 'category';
    public const TABLE_NAME = 'app_messagelog';
    public const PRIMARY_NAME = 'log_id';

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordClassName()
     */
    public function getRecordClassName() : string
    {
        return Application_Messagelogs_Log::class;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordFiltersClassName()
     */
    public function getRecordFiltersClassName() : string
    {
        return Application_Messagelogs_FilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return Application_Messagelogs_FilterSettings::class;
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordDefaultSortKey()
     */
    public function getRecordDefaultSortKey() : string
    {
        return 'date';
    }

    public function getRecordDefaultSortDir() : string
    {
        return self::SORT_DIR_DESC;
    }
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordSearchableColumns()
     */
    public function getRecordSearchableColumns() : array
    {
        return array(
            self::COL_MESSAGE => t('Message'),
            self::COL_CATEGORY => t('Category')
        );
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTableName()
     */
    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordPrimaryName()
     */
    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTypeName()
     */
    public function getRecordTypeName() : string
    {
        return 'appmessagelog';
    }
    
    public function getAvailableCategories() : array
    {
        return array_map('strval', $this->getDistinctValues(self::COL_CATEGORY));
    }

    /**
     * @return string[]
     */
    public function getAvailableTypes() : array
    {
        return array_map('strval', $this->getDistinctValues('type'));
    }

    /**
     * @return int[]
     */
    public function getAvailableUserIDs() : array
    {
        return array_map('intval', $this->getDistinctValues('user_id'));
    }

    private function getDistinctValues(string $keyName) : array
    {
        return DBHelper::fetchAllKey(
            $keyName,
            "SELECT DISTINCT
                `".$keyName."`
            FROM
                `".$this->getRecordTableName()."`
            ORDER BY
                `".$keyName."` ASC"
        );
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getCollectionLabel()
     */
    public function getCollectionLabel() : string
    {
        return t('Message logs');
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordLabel()
     */
    public function getRecordLabel() : string
    {
        return t('Message log entry');
    }

    public function addInfo(string $message, string $category='', ?Application_User $user=null) : Application_Messagelogs_Log
    {
        return $this->logMessage(self::MESSAGELOG_INFORMATION, $message, $category, $user);
    }
    
    public function addError(string $message, string $category='', ?Application_User $user=null) : Application_Messagelogs_Log
    {
        return $this->logMessage(self::MESSAGELOG_ERROR, $message, $category, $user);
    }
    
    public function addWarning(string $message, string $category='', ?Application_User $user=null) : Application_Messagelogs_Log
    {
        return $this->logMessage(self::MESSAGELOG_WARNING, $message, $category, $user);
    }
    
   /**
    * Logs a system message. These messages are logged into the
    * <code>app_messagelog</code> database table, and intended to
    * be viewable via the UI. It should be used to log messages that
    * are relevant to content, messages that are tracked nowhere else
    * like the destruction of a revisionable item.
    *
    * @param string $type Identifier string for the operation, any alphanumerical string.
    * @param string $message The log message.
    * @param string $category Used to categorize messages in the UI.
    * @param Application_User $user
    * @return Application_Messagelogs_Log
    */
    private function logMessage(string $type, string $message, string $category='', ?Application_User $user=null) : Application_Messagelogs_Log
    {
        if(empty($category)) 
        {
            $category = '';
        }
        
        if(empty($user)) 
        {
            $user = Application::getUser();
        }

        return $this->createNewRecord(array(
            'date' => date('Y-m-d H:i:s'),
            'type' => $type,
            self::COL_MESSAGE => $message,
            self::COL_CATEGORY => $category,
            'user_id' => $user->getID()
        ));
    }

    public function generateDeveloperTestEntries() : void
    {
        DBHelper::requireTransaction('Generate message log developer test entries');

        $users = array();
        foreach(AppFactory::createUsers()->getAll() as $appUser) {
            $users[] = $appUser->getUserInstance();
        }

        $categories = array(
            'Deleted a record',
            'Exported a product list',
            'Dumped the database',
            'Ran a complex search',
            'Did something fishy',
            'Uploaded a document',
            'Ran the temporary storage cleanup',
            'Reset the application cache',
            'Generated message log entries',
            'Found true love',
            'Tried to build a perpetual motion machine',
            'Found the answer'
        );

        for($i=0; $i < 142; $i++) {
            $this->logMessage(
                self::MESSAGE_TYPES[array_rand(self::MESSAGE_TYPES)],
                'Generated developer test message #'.$i,
                $categories[array_rand($categories)],
                $users[array_rand($users)]
            );
        }
    }
}
