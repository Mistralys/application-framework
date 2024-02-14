<?php

declare(strict_types=1);

namespace TestDriver\Area\RevisionableScreen;

use Application_Admin_Area_Mode_RevisionableList;
use Application_RevisionableCollection;
use Application_RevisionableCollection_DBRevisionable;
use Application_Traits_Admin_RevisionableList;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableListScreen extends Application_Admin_Area_Mode_RevisionableList
{
    public const URL_NAME = 'list';

    public function getCollection(): Application_RevisionableCollection
    {
        return RevisionableCollection::getInstance();
    }

    protected function getEntryData(Application_RevisionableCollection_DBRevisionable $revisionable) : array
    {
        return array();
    }

    protected function configureColumns() : void
    {
    }

    protected function configureActions() : void
    {
    }

    public function getBackOrCancelURL(): string
    {
        // TODO: Implement getBackOrCancelURL() method.
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available revisionables');
    }
}
