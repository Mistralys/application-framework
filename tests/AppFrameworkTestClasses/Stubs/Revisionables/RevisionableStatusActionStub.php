<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Revisionables;

use Application\Revisionable\Admin\Screens\Action\BaseRevisionableStatusAction;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use TestDriver\Revisionables\RevisionableCollection;
use UI_PropertiesGrid;

class RevisionableStatusActionStub extends BaseRevisionableStatusAction
{
    public const string URL_NAME = 'status-stub';

    protected function injectProperties(UI_PropertiesGrid $grid) : void
    {
    }

    public function createCollection(): RevisionableCollectionInterface
    {
        return RevisionableCollection::getInstance();
    }
}
