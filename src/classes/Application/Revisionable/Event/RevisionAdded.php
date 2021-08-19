<?php

declare(strict_types=1);

class Application_Revisionable_Event_RevisionAdded
{
    /**
     * @var Application_Revisionable_Interface
     */
    private $revisionable;

    /**
     * @var Application_RevisionStorage_Event_RevisionAdded
     */
    private $originalEvent;

    public function __construct(Application_Revisionable_Interface $revisionable, Application_RevisionStorage_Event_RevisionAdded $originalEvent)
    {
        $this->revisionable = $revisionable;
        $this->originalEvent = $originalEvent;
    }

    /**
     * @return Application_Revisionable_Interface
     */
    public function getRevisionable() : Application_Revisionable_Interface
    {
        return $this->revisionable;
    }

    public function getNumber() : int
    {
        return $this->originalEvent->getNumber();
    }

    public function getTimestamp() : int
    {
        return $this->originalEvent->getTimestamp();
    }

    public function getOwnerID() : int
    {
        return $this->originalEvent->getOwnerID();
    }

    public function getComments() : string
    {
        return $this->originalEvent->getComments();
    }

    public function getOwnerName() : string
    {
        return $this->originalEvent->getOwnerName();
    }
}
