<?php

declare(strict_types=1);

namespace TestDriver\Area\RevisionableScreen;

use Application\Admin\Area\Mode\RevisionableCreateScreen;
use Application\Revisionable\RevisionableInterface;
use TestDriver\Revisionables\RevisionableCollection;

class CreateRevisionableScreen extends RevisionableCreateScreen
{
    public const URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Create');
    }

    public function getTitle(): string
    {
        return t('Create a revisionable');
    }

    protected function createCollection()
    {
        return RevisionableCollection::getInstance();
    }

    protected function processSettings(array $formValues): RevisionableInterface
    {
        $collection = RevisionableCollection::getInstance();

        return $collection->createNewRevisionable('Label', 'Alias');
    }

    protected function injectFormElements(): void
    {
        // TODO: Implement injectFormElements() method.
    }

    protected function getDefaultFormData(): array
    {
        return array();
    }

    public function isUserAllowed(): bool
    {
        return true;
    }
}
