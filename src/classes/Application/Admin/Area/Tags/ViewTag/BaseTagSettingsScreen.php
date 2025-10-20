<?php

declare(strict_types=1);

namespace Application\Area\Tags\ViewTag;

use Application\AppFactory;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use Application_Formable_RecordSettings;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsScreen;
use DBHelper_BaseRecord;

/**
 * @property TagRecord $record
 */
abstract class BaseTagSettingsScreen extends BaseRecordSettingsScreen
{
    public const string URL_NAME = 'tag-settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
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