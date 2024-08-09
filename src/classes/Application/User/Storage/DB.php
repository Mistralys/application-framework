<?php
/**
 * File containing the {@link Application_User_Storage_DB} class
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Storage_DB
 */

declare(strict_types=1);

/**
 * DB storage for users: saves all user-related data to the
 * database in the <code>user_settings</code> table.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_User_Storage_DB extends Application_User_Storage
{
    const TABLE_NAME = 'user_settings';

    const COL_SETTING_NAME = 'setting_name';
    const COL_SETTING_VALUE = 'setting_value';
    const COL_USER_ID = Application_Users::PRIMARY_NAME;

    /**
     * @return array<string,string>
     * @throws DBHelper_Exception
     */
    public function load() : array
    {
        $items = DBHelper::fetchAll(self::statement("
            SELECT
                {name},
                {value}
            FROM
                {table_settings}
            WHERE
                {users_primary}=:user_id"
            ),
            array(
                ':user_id' => $this->userID
            )
        );
        
        $data = array();
        foreach ($items as $item) {
            $data[$item[self::COL_SETTING_NAME]] = $item[self::COL_SETTING_VALUE];
        }
        
        return $data;
    }

    public static function statement(string $template) : DBHelper_StatementBuilder
    {
        return statementBuilder($template, self::statementValues());
    }

    public static function statementValues() : DBHelper_StatementBuilder_ValuesContainer
    {
        return statementValues()
            ->table('{table_settings}', self::TABLE_NAME)
            ->field('{users_primary}', Application_Users::PRIMARY_NAME)
            ->field('{name}', self::COL_SETTING_NAME)
            ->field('{value}', self::COL_SETTING_VALUE);
    }
    
    public function reset(?string $prefix=null) : void
    {
        DBHelper::requireTransaction('Reset user settings');

        if($prefix !== null) {
            $this->resetByPrefix($prefix);
            return;
        }
        
        DBHelper::deleteRecords(
            self::TABLE_NAME,
            array(
                self::COL_USER_ID => $this->userID
            )
        );
    }

    private function resetByPrefix(string $prefix) : void
    {
        $query = <<<'SQL'
DELETE FROM
    {table_settings}
WHERE
    {users_primary}=:user_id
AND 
    {name} LIKE '{prefix}%'
SQL;

        $statement = self::statement($query)
            ->val('prefix', $prefix);

        DBHelper::delete(
            $statement,
            array(
                ':user_id' => $this->userID
            )
        );
    }

    /**
     * @param array<string,string> $data
     * @throws DBHelper_Exception
     */
    public function save(array $data) : void
    {
        $transaction = false;
        
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
            $transaction = true;
        }
        
        foreach($data as $name => $value) 
        {
            DBHelper::insertOrUpdate(
                self::TABLE_NAME,
                array(
                    self::COL_USER_ID => $this->userID,
                    self::COL_SETTING_NAME => $name,
                    self::COL_SETTING_VALUE => $value
                ),
                array(
                    self::COL_USER_ID,
                    self::COL_SETTING_NAME
                )
            );
        }
        
        if($transaction) {
            DBHelper::commitTransaction();
        }
    }
    
    public function removeKey(string $name) : void
    {
        DBHelper::deleteRecords(
            self::TABLE_NAME,
            array(
                self::COL_USER_ID => $this->userID,
                self::COL_SETTING_NAME => $name
            )
        );
    }
}