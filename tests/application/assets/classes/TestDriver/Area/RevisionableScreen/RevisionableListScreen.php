<?php

declare(strict_types=1);

namespace TestDriver\Area\RevisionableScreen;

use Application\Revisionable\Collection\BaseRevisionableCollection;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\Admin\Screens\Mode\BaseRevisionableListMode;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableListScreen extends BaseRevisionableListMode
{
    public function createCollection(): RevisionableCollection
    {
        return RevisionableCollection::getInstance();
    }

    protected function getEntryData(RevisionableInterface $revisionable) : array
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
        return APP_URL;
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
