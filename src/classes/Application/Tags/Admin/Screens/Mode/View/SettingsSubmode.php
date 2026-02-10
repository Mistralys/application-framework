<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Screens\Mode\View;

use Application\AppFactory;
use Application\Tags\Admin\TagScreenRights;
use Application\Tags\Admin\Traits\ViewSubmodeInterface;
use Application\Tags\Admin\Traits\ViewSubmodeTrait;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use Application\Tags\TagSettingsManager;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @property TagRecord $record
 */
class SettingsSubmode extends BaseRecordSettingsSubmode implements ViewSubmodeInterface
{
    use ViewSubmodeTrait;

    public const string URL_NAME = 'tag-settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TagScreenRights::SCREEN_VIEW_SETTINGS;
    }

    public function getFeatureRights(): array
    {
        return array(
            t('Edit the settings') => TagScreenRights::SCREEN_VIEW_SETTINGS_EDIT,
        );
    }

    public function getTitle(): string
    {
        return t('Edit tag settings');
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(TagScreenRights::SCREEN_VIEW_SETTINGS_EDIT);
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The tag settings have been saved successfully at %1$s.',
            sb()->time()
        );
    }

    public function getSettingsManager(): ?TagSettingsManager
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }
}