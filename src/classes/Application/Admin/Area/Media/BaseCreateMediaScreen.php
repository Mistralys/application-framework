<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media;

use Application\AppFactory;
use Application\Media\Admin\MediaScreenRights;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Media\Collection\MediaSettingsManager;
use DBHelper\Admin\Screens\Submode\BaseRecordCreateSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @property MediaRecord|NULL $record
 * @property MediaCollection $collection
 */
abstract class BaseCreateMediaScreen extends BaseRecordCreateSubmode
{
    public const string URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_CREATE;
    }

    public function getSettingsManager() : MediaSettingsManager
    {
        return MediaCollection::createSettingsManager($this, $this->record);
    }

    public function createCollection() : MediaCollection
    {
        return AppFactory::createMediaCollection();
    }

    public function getSuccessURL(DBHelperRecordInterface $record): string
    {
        if($record instanceof MediaRecord) {
            if($record->isTaggingEnabled()) {
                return (string)$record->adminURL()->tagging();
            }

            return (string)$record->adminURL()->status();
        }

        return parent::getSuccessURL($record);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
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

    public function getTitle(): string
    {
        return t('Add a media file');
    }

    public function getNavigationTitle(): string
    {
        return t('Add media');
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();
    }

    protected function _handleSidebar(): void
    {
        parent::_handleSidebar();

        if($this->collection->isTaggingEnabled())
        {
            $this->sidebar->addSeparator();

            $this->sidebar->addInfoMessage(
                sb()
                    ->note()
                    ->t('Once the document has been added, you can assign it tags.'),
                true,
                false
            );
        }
    }
}
