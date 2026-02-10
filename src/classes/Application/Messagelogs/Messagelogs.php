<?php
/**
 * @package Application
 * @subpackage Logging
 */

use Application\AppFactory;
use Application\Application;
use DBHelper\BaseCollection\DBHelperCollectionInterface;

/**
 * Used to add and retrieve messages stored in the
 * database, in the {@see self::TABLE_NAME} table.
 * This is intended to be used to messages that need
 * to be persisted.
 * 
 * The messages can be viewed in the UI.
 * 
 * @package Application
 * @subpackage Logging
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method Application_Messagelogs_FilterCriteria getFilterCriteria()
 * @method Application_Messagelogs_Log getByID(int $log_id)
 * @method Application_Messagelogs_FilterSettings getFilterSettings()
 * @method Application_Messagelogs_Log createNewRecord(array $data=array(), bool $silent=false)
 */
class Application_Messagelogs extends DBHelper_BaseCollection
{
    public const string MESSAGELOG_INFORMATION = 'info';
    public const string MESSAGELOG_ERROR = 'error';
    public const string MESSAGELOG_WARNING = 'warning';
    public const array MESSAGE_TYPES = array(
        self::MESSAGELOG_INFORMATION,
        self::MESSAGELOG_ERROR,
        self::MESSAGELOG_WARNING
    );
    public const string COL_MESSAGE = 'message';
    public const string COL_CATEGORY = 'category';
    public const string TABLE_NAME = 'app_messagelog';
    public const string PRIMARY_NAME = 'log_id';

    public function getRecordClassName() : string
    {
        return Application_Messagelogs_Log::class;
    }

    public function getRecordFiltersClassName() : string
    {
        return Application_Messagelogs_FilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return Application_Messagelogs_FilterSettings::class;
    }
    
    public function getRecordDefaultSortKey() : string
    {
        return 'date';
    }

    public function getRecordDefaultSortDir() : string
    {
        return DBHelperCollectionInterface::SORT_DIR_DESC;
    }
    
    public function getRecordSearchableColumns() : array
    {
        return array(
            self::COL_MESSAGE => t('Message'),
            self::COL_CATEGORY => t('Category')
        );
    }

    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }

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

    public function getCollectionLabel() : string
    {
        return t('Message logs');
    }

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
    * @param Application_User|NULL $user
    * @return Application_Messagelogs_Log
    */
    private function logMessage(string $type, string $message, string $category='', ?Application_User $user=null) : Application_Messagelogs_Log
    {
        if(empty($category)) 
        {
            $category = '';
        }
        
        if($user === null)
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
