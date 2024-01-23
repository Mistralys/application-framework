<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application;
use Application\Admin\Area\BaseMediaLibraryScreen;
use Application\Admin\Area\Media\BaseCreateMediaScreen;
use Application\Admin\Area\Media\BaseImageGalleryScreen;
use Application\Admin\Area\Media\BaseMediaListScreen;
use Application\AppFactory;
use Application\Media\MediaTagContainer;
use Application_Admin_ScreenInterface;
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
class MediaCollection extends DBHelper_BaseCollection implements Application\Tags\Taggables\TagCollectionInterface
{
    use Application\Tags\Taggables\TagCollectionTrait;

    public const TABLE_NAME = 'media';
    public const PRIMARY_NAME = 'media_id';
    public const MEDIA_TYPE = 'media';

    public const COL_USER_ID = 'user_id';
    public const COL_DATE_ADDED = 'media_date_added';
    public const COL_TYPE = 'media_type';
    public const COL_NAME = 'media_name';
    public const COL_EXTENSION = 'media_extension';
    public const COL_SIZE = 'file_size';
    public const COL_KEYWORDS = 'keywords';
    public const COL_DESCRIPTION = 'description';

    private static ?bool $hasSizeColumn = null;

    public static function hasSizeColumn() : bool
    {
        if(!isset(self::$hasSizeColumn))
        {
            self::$hasSizeColumn = DBHelper::columnExists(self::TABLE_NAME, self::COL_SIZE);
        }

        return self::$hasSizeColumn;
    }

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

    public function getAdminListURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseMediaListScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminImageGalleryURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseImageGalleryScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminUpdateSizesURL(array $params=array(), bool $simulate=false) : string
    {
        $params[BaseMediaListScreen::REQUEST_PARAM_UPDATE_SIZES] = 'yes';

        if($simulate) {
            $params[Application::REQUEST_VAR_SIMULATION] = 'yes';
        }

        return $this->getAdminURL($params);
    }

    public function getAdminCreateURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseCreateMediaScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = BaseMediaLibraryScreen::URL_NAME;

        return AppFactory::createRequest()
            ->buildURL($params);
    }

    public static function createSettingsManager(Application_Formable $formable, ?MediaRecord $record=null) : MediaSettingsManager
    {
        return new MediaSettingsManager($formable, $record);
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

    public function getTagContainerClass(): ?string
    {
        return MediaTagContainer::class;
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
}
