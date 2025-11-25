<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Revisionables;

use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\Admin\Screens\Action\BaseRevisionableRecordAction;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableRecordActionStub extends BaseRevisionableRecordAction
{
    public const string URL_NAME = 'action-stub';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

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
}
