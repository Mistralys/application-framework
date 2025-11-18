<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Revisionables;

use Application\Revisionable\Admin\Screens\Submode\BaseRevisionableListSubmode;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableInterface;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableListSubmodeStub extends BaseRevisionableListSubmode
{
    public const string URL_NAME = 'list-stub';

    public function getNavigationTitle(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return '';
    }

    public function createCollection(): RevisionableCollectionInterface
    {
        return RevisionableCollection::getInstance();
    }

    public function getBackOrCancelURL(): string
    {
        return '';
    }

    protected function getEntryData(RevisionableInterface $revisionable): array
    {
        return array();
    }

    protected function configureColumns() : void
    {
    }

    protected function configureActions() : void
    {
    }
}
