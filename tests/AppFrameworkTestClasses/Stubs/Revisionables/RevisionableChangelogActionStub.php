<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Revisionables;

use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\Admin\Screens\Action\BaseRevisionableChangelogAction;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableChangelogActionStub extends BaseRevisionableChangelogAction
{
    public const string URL_NAME = 'changelog-stub';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    protected function getPersistVars(): array
    {
        return array();
    }

    public function createCollection(): RevisionableCollectionInterface
    {
        return RevisionableCollection::getInstance();
    }
}
