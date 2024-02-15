<?php

declare(strict_types=1);

namespace Application\Area\Tags\ViewTag;

use Application\AppFactory;
use Application_Admin_Area_Mode_Submode_CollectionEdit;
use Application_Formable_RecordSettings;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use DBHelper_BaseRecord;

/**
 * @property TagRecord $record
 */
abstract class BaseTagSettingsScreen extends Application_Admin_Area_Mode_Submode_CollectionEdit
{
    public const URL_NAME = 'tag-settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function isUserAllowedEditing(): bool
    {
        return true;
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function createCollection() : TagCollection
    {
        return AppFactory::createTags();
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The tag settings have been saved successfully at %1$s.',
            sb()->time()
        );
    }

    public function getSettingsManager(): ?Application_Formable_RecordSettings
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getTitle(): string
    {
        return t('Edit tag settings');
    }
}