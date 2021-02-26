<?php
/**
 * File containing the {@link Application_User_Storage_DB} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Storage_DB
 */

/**
 * DB storage for users: saves all user-related data to the
 * database in the <code>user_settings</cide> table.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_User_Storage_DB extends Application_User_Storage
{
    public function load()
    {
        $items = DBHelper::fetchAll(
            'SELECT
                setting_name,
                setting_value
            FROM
                user_settings
            WHERE
                user_id=:user_id',
            array(
                ':user_id' => $this->userID
            )
        );
        
        $data = array();
        foreach ($items as $item) {
            $data[$item['setting_name']] = $item['setting_value'];
        }
        
        return $data;
    }
    
    public function reset()
    {
        DBHelper::requireTransaction('Reset user settings');
        
        DBHelper::deleteRecords(
            'user_settings',
            array(
                'user_id' => $this->userID
            )
        );
    }
    
    public function save($data)
    {
        $transaction = false;
        
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
            $transaction = true;
        }
        
        foreach($data as $name => $value) 
        {
            DBHelper::insertOrUpdate(
                'user_settings',
                array(
                    'user_id' => $this->userID,
                    'setting_name' => $name,
                    'setting_value' => $value
                ),
                array('user_id', 'setting_name')
            );
        }
        
        if($transaction) {
            DBHelper::commitTransaction();
        }
    }
    
    public function removeKey($name)
    {
        DBHelper::deleteRecords(
            'user_settings',
            array(
                'user_id' => $this->userID,
                'setting_name' => $name
            )
        );
    }
}