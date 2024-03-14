<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableStatelessInterface;

class Application_Revisionable_Event_RevisionAdded
{
    /**
     * @var RevisionableStatelessInterface
     */
    private $revisionable;

    /**
     * @var Application_RevisionStorage_Event_RevisionAdded
     */
    private $originalEvent;

    public function __construct(RevisionableStatelessInterface $revisionable, Application_RevisionStorage_Event_RevisionAdded $originalEvent)
    {
        $this->revisionable = $revisionable;
        $this->originalEvent = $originalEvent;
    }

    /**
     * @return RevisionableStatelessInterface
     */
    public function getRevisionable() : RevisionableStatelessInterface
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
