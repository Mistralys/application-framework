<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;

abstract class Application_Admin_Area_Mode_Submode_Revisionable extends Application_Admin_Area_Mode_Submode
{
    protected Application_RevisionableCollection $collection;
    protected string $recordTypeName;
    protected int $revisionableID;
    protected RevisionableInterface $revisionable;
    
    protected function init() : void
    {
        $this->collection = $this->createCollection();
        $this->recordTypeName = $this->collection->getRecordTypeName();
    }

    /**
     * @return Application_RevisionableCollection
     */
    abstract protected function createCollection();

    /**
     * Retrieves the revisionable ID from the request, and attempts to retrieve
     * the instance. Stores the instance in the {@link $revisionable} property
     * on success.
     *
     * @throws Application_Exception
     * @return RevisionableInterface
     */
    protected function requireRevisionable() : RevisionableInterface
    {
        $collection = $this->createCollection();
        $revisionable = $collection->getByRequest();

        if($revisionable === null) {
            $this->redirectTo($collection->getAdminListURL());
        }

        $this->revisionable = $revisionable;
        $this->revisionableID = $revisionable->getID();
        return $this->revisionable;
    }
}