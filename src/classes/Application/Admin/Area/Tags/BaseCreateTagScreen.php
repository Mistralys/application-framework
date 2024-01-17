<?php

declare(strict_types=1);

namespace Application\Area\Tags;

use Application\AppFactory;
use Application_Admin_Area_Mode_CollectionCreate;
use Application_Formable_RecordSettings;
use Application\Tags\TagCollection;
use DBHelper_BaseRecord;
use UI;

abstract class BaseCreateTagScreen extends Application_Admin_Area_Mode_CollectionCreate
{
    public const URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function createCollection() : TagCollection
    {
        return AppFactory::createTags();
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The tag %1$s has been created successfully at %2$s.',
            $record->getLabel(),
            sb()->time()
        );
    }

    /**
     * @return Application_Formable_RecordSettings|NULL
     */
    public function getSettingsManager(): ?Application_Formable_RecordSettings
    {
        return $this->createCollection()->createSettingsManager($this, null);
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return t('Create a tag');
    }

    public function getNavigationTitle(): string
    {
        return t('Create tag');
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setIcon(UI::icon()->tags());
    }
}
