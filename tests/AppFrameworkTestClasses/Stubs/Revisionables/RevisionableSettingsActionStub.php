<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Revisionables;

use Application\Revisionable\Admin\Screens\Action\BaseRevisionableSettingsAction;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableInterface;
use TestDriver\Revisionables\RevisionableCollection;

class RevisionableSettingsActionStub extends BaseRevisionableSettingsAction
{
    public const string URL_NAME = 'settings-stub';


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

    protected function getBackOrCancelURL(): string
    {
        return '';
    }

    protected function processSettings(array $formValues): RevisionableInterface
    {
        return $this->requireRevisionable();
    }

    protected function injectFormElements(): void
    {
    }

    protected function getDefaultFormData(): array
    {
        return array();
    }
}
