<?php

/**
 * Helper class that is used to keep track of database operations,
 * and retrieve information about operation types.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_OperationTypes
{
    const int TYPE_INSERT = 1;
    const int TYPE_UPDATE = 2;
    const int TYPE_DELETE = 3;
    const int TYPE_DROP = 4;
    const int TYPE_SET = 5;
    const int TYPE_SHOW = 6;
    const int TYPE_SELECT = 7;
    const int TYPE_TRUNCATE = 8;
    const int TYPE_TRANSACTION = 9;
    const int TYPE_ALTER = 10;
    
    protected static $typeDefs = array();
    
    /**
     * Checks if the specified type ID is a write operation.
     * @param int $typeID
     * @return boolean
     */
    public static function isWriteOperation($typeID)
    {
        if(isset(self::$typeDefs[$typeID])) {
            return self::$typeDefs[$typeID]['isWriteOperation'];
        }
        
        return false;
    }
    
    protected static $initDone = false;
    
    public static function init()
    {
        if(self::$initDone) {
            return;
        }
        
        self::$initDone= true;
        
        self::registerType(self::TYPE_INSERT, true);
        self::registerType(self::TYPE_UPDATE, true);
        self::registerType(self::TYPE_DELETE, true);
        self::registerType(self::TYPE_DROP, true);
        self::registerType(self::TYPE_SET, false);
        self::registerType(self::TYPE_SHOW, false);
        self::registerType(self::TYPE_SELECT, false);
        self::registerType(self::TYPE_TRUNCATE, true);
        self::registerType(self::TYPE_TRANSACTION, false);
        self::registerType(self::TYPE_ALTER, true);
    }
    
    /**
     * Retrieves all type IDs for operations that write to the database.
     * @return int[]
     */
    public static function getWriteTypes()
    {
        $types = array();
        foreach(self::$typeDefs as $typeID => $def) {
            if($def['isWriteOperation']) {
                $types[] = $typeID;
            }
        }
        
        return $types;
    }
    
    protected static function registerType($typeID, $isWriteOperation)
    {
        self::$typeDefs[$typeID] = array(
            'isWriteOperation' => $isWriteOperation
        );
    }
}
