<?php

declare(strict_types=1);

use function AppUtils\parseVariable;

class DBHelper_BaseCollection_Event_BeforeCreateRecord extends Application_EventHandler_Event
{
    const ERROR_INVALID_COLLECTION_ARGUMENT = 55101;
    
    public function getCollection() : DBHelper_BaseCollection
    {
        $collection = $this->getArgument(0);
        
        if($collection instanceof DBHelper_BaseCollection)
        {
            return $collection;
        }
        
        throw new DBHelper_Exception(
            'Internal error: event does not have expected collection.',
            sprintf(
                'Expected [%s], but got [%s].',
                DBHelper_BaseCollection::class,
                parseVariable($collection)->enableType()->toString()
            ),
            self::ERROR_INVALID_COLLECTION_ARGUMENT
        );
    }
    
    public function getRecordData() : array
    {
        $data = $this->getArgument(1);
        
        if(is_array($data))
        {
            return $data;
        }
        
        return array();
    }
    
    public function getName() : string
    {
        $data = $this->getRecordData();

        if(isset($data['name']))
        {
            return $data['name'];
        }
        
        return '';
    }
}
