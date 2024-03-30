<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media;

use Application\Admin\Area\Mode\BaseCollectionCreateExtended;
use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Media\Collection\MediaSettingsManager;
use DBHelper_BaseRecord;

/**
 * @property MediaRecord|NULL $record
 * @property MediaCollection $collection
 */
abstract class BaseCreateMediaScreen extends BaseCollectionCreateExtended
{
    public const URL_NAME = 'create';

    public function getURLName(): string
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

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The media file %1$s has been added successfully at %2$s.',
            $record->getLabel(),
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canCreateMedia();
    }

    public function getTitle(): string
    {
        return t('Add a media file');
    }
}
