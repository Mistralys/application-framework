<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application;
use Application\AppFactory;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Media\Admin\MediaAdminURLs;
use Application\Media\Admin\Screens\MediaLibraryArea;
use Application\Media\MediaTagConnector;
use Application\Media\Events\RegisterMediaTagsListener;
use Application\Tags\TagCollection;
use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\Taggables\TagCollectionTrait;
use Application\Tags\TagRecord;
use Application_Formable;
use Application_Media;
use AppUtils\Microtime;
use DBHelper;
use DBHelper_BaseCollection;

/**
 * @method MediaRecord getByID(int $id)
 * @method MediaFilterCriteria getFilterCriteria()
 * @method MediaFilterSettings getFilterSettings()
 */
class MediaCollection extends DBHelper_BaseCollection implements TagCollectionInterface
{
    public const string COLLECTION_ID = 'media';

    use TagCollectionTrait;

    public const string RECENT_ITEMS_CATEGORY = 'recent_media';
    public const string TABLE_NAME = 'media';
    public const string PRIMARY_NAME = 'media_id';
    public const string MEDIA_TYPE = 'media';

    public const string COL_USER_ID = 'user_id';
    public const string COL_DATE_ADDED = 'media_date_added';
    public const string COL_TYPE = 'media_type';
    public const string COL_NAME = 'media_name';
    public const string COL_EXTENSION = 'media_extension';
    public const string COL_SIZE = 'file_size';
    public const string COL_KEYWORDS = 'keywords';
    public const string COL_DESCRIPTION = 'description';

    public function getCollectionID(): string
    {
        return self::COLLECTION_ID;
    }

    public function getCollectionRegistrationClass(): string
    {
        return RegisterMediaTagsListener::class;
    }

    private static ?bool $hasSizeColumn = null;

    public static function hasSizeColumn() : bool
    {
        if(!isset(self::$hasSizeColumn))
        {
            self::$hasSizeColumn = DBHelper::columnExists(self::TABLE_NAME, self::COL_SIZE);
        }

        return self::$hasSizeColumn;
    }

    // region: X - Interface methods

    public function getRecordClassName(): string
    {
        return MediaRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return MediaFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return MediaFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_DATE_ADDED;
    }

    public function getRecordSearchableColumns(): array
    {
        $columns = array(
            self::COL_NAME => t('File name')
        );

        if(self::hasSizeColumn()) {
            $columns[self::COL_KEYWORDS] = t('Keywords');
            $columns[self::COL_DESCRIPTION] = t('Description');
        }

        return $columns;
    }

    public function getRecordTableName(): string
    {
        return Application_Media::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return Application_Media::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return 'media_record';
    }

    public function getCollectionLabel(): string
    {
        return t('Media files');
    }

    public function getRecordLabel(): string
    {
        return t('Media file');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    // endregion: X - Interface methods

    private static ?MediaAdminURLs $adminURLs = null;

    public function adminURL() : MediaAdminURLs
    {
        if(!isset(self::$adminURLs)) {
            self::$adminURLs = new MediaAdminURLs();
        }

        return self::$adminURLs;
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = MediaLibraryArea::URL_NAME;

        return AppFactory::createRequest()
            ->buildURL($params);
    }

    public static function createSettingsManager(Application_Formable $formable, ?MediaRecord $record=null) : MediaSettingsManager
    {
        return new MediaSettingsManager($formable, $record);
    }

    public function isTaggingEnabled() : bool
    {
        return TagCollection::tableExists() && $this->getRootTag() !== null;
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_NAME)
            ->makeRequired();

        $this->keys->register(self::COL_USER_ID)
            ->makeRequired()
            ->setDefault((string)Application::getUser()->getID());

        $this->keys->register(self::COL_DATE_ADDED)
            ->makeRequired()
            ->setGenerator(function () : string {
                return Microtime::createNow()->getMySQLDate();
            });

        $this->keys->register(self::COL_EXTENSION)
            ->makeRequired();

        $this->keys->register(self::COL_TYPE)
            ->makeRequired();

        if(self::hasSizeColumn()) {
            $this->keys->register(self::COL_SIZE)
                ->makeRequired();
        }
    }

    public function updateFileSizes() : void
    {
        if(!self::hasSizeColumn()) {
            $this->log('UpdateFileSizes | Size column not present.');
            return;
        }

        $this->log('UpdateFileSizes | Starting the update process.');

        DBHelper::requireTransaction('Update all media file sizes');

        Application::setTimeLimit(0, 'Update all media file sizes');

        $ids = $this->getFilterCriteria()->getIDs();

        $this->log('UpdateFileSizes | Found [%s] media files.', count($ids));

        foreach($ids as $id)
        {
            $this->getByID($id)
                ->refreshFileSize()
                ->dispose();
        }
    }

    // region: Tagging

    public function getTaggableTypeLabel() : string
    {
        return t('Media document');
    }

    public function getTaggableByID(int $id): MediaRecord
    {
        return $this->getByID($id);
    }

    public function getTagConnectorClass(): ?string
    {
        return MediaTagConnector::class;
    }

    public function getTagPrimary(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getTagTable(): string
    {
        return Application_Media::TABLE_TAGS;
    }

    public function getTagSourceTable(): string
    {
        return Application_Media::TABLE_NAME;
    }

    public function getRootTag(): ?TagRecord
    {
        return AppFactory::createMedia()->getRootTag();
    }

    public function getTagRegistryKey(): string
    {
        return Application_Media::TAG_REGISTRY_KEY;
    }

    // endregion: Tagging
}
