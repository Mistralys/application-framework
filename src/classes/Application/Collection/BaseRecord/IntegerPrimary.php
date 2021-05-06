<?php

declare(strict_types=1);

abstract class Application_Collection_BaseRecord_IntegerPrimary extends Application_Collection_BaseRecord
{
    /**
     * @var int
     */
    private $recordID;

    /**
     * @param int $recordID
     * @throws Application_Exception
     * @see Application_Collection_BaseRecord::ERROR_COULD_NOT_LOAD_DATA
     */
    public function __construct(int $recordID)
    {
        $this->recordID = $recordID;

        $this->initRecord();
    }

    public function getID() : int
    {
        return $this->recordID;
    }
}
