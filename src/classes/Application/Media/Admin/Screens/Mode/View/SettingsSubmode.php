<?php

declare(strict_types=1);

namespace Application\Media\Admin\Screens\Mode\View;

use Application\Media\Admin\MediaScreenRights;
use Application\Media\Admin\Traits\MediaViewInterface;
use Application\Media\Admin\Traits\MediaViewTrait;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Media\Collection\MediaSettingsManager;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @property MediaRecord $record
 */
class SettingsSubmode extends BaseRecordSettingsSubmode implements MediaViewInterface
{
    use MediaViewTrait;

    public const string URL_NAME = 'settings';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_VIEW_SETTINGS;
    }

    public function getFeatureRights() : array
    {
        return array(
            t('Edit the settings') => MediaScreenRights::SCREEN_VIEW_SETTINGS_EDIT
        );
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(MediaScreenRights::SCREEN_VIEW_SETTINGS_EDIT);
    }

    public function getSettingsManager() : MediaSettingsManager
    {
        return MediaCollection::createSettingsManager($this, $this->record);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The media file %1$s has been updated successfully at %2$s.',
            $record->getLabel(),
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function isEditable(): bool
    {
        return $this->record->isEditable();
    }

    public function getTitle(): string
    {
        return t('Settings');
    }
}
