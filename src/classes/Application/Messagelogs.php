<?php
/**
 * File containing the {@see Application_Messagelogs} class.
 * 
 * @package Application
 * @subpackage Logging
 * @see Application_Messagelogs
 */

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
    const MESSAGELOG_INFORMATION = 'info';
    const MESSAGELOG_ERROR = 'error';
    const MESSAGELOG_WARNING = 'warning';
    
    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordClassName()
     */
    public function getRecordClassName() : string
    {
        return 'Application_Messagelogs_Log';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordFiltersClassName()
     */
    public function getRecordFiltersClassName() : string
    {
        return 'Application_Messagelogs_FilterCriteria';
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return 'Application_Messagelogs_FilterSettings';
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
            'message' => t('Message'),
            'category' => t('Category')
        );
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTableName()
     */
    public function getRecordTableName() : string
    {
        return 'app_messagelog';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordPrimaryName()
     */
    public function getRecordPrimaryName() : string
    {
        return 'log_id';
    }

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordTypeName()
     */
    public function getRecordTypeName() : string
    {
        return 'appmessagelog';
    }
    
    public function getAvailableCategories()
    {
        return DBHelper::fetchAllKey(
            'category', 
            "SELECT DISTINCT
                `category`
            FROM
                `".$this->getRecordTableName()."`
            ORDER BY
                `category` ASC"
        );
    }
    
    public function getAvailableTypes()
    {
        return DBHelper::fetchAllKey(
            'type',
            "SELECT DISTINCT
                `type`
            FROM
                `".$this->getRecordTableName()."`
            ORDER BY
                `type` ASC"
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

    /**
     * {@inheritDoc}
     * @see DBHelper_BaseCollection::getRecordProperties()
     */
    public function getRecordProperties() : array
    {
        return array();
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
            'message' => $message,
            'category' => $category,
            'user_id' => $user->getID()
        ));
    }
}
