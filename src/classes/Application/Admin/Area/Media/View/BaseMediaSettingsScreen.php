<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media\View;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Media\Collection\MediaSettingsManager;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @property MediaRecord $record
 */
abstract class BaseMediaSettingsScreen extends BaseRecordSettingsSubmode
{
    public const string URL_NAME = 'settings';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getSettingsManager() : MediaSettingsManager
    {
        return MediaCollection::createSettingsManager($this, $this->record);
    }

    public function createCollection() : MediaCollection
    {
        return AppFactory::createMediaCollection();
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

    public function isUserAllowedEditing(): bool
    {
        return $this->user->canEditMedia();
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
