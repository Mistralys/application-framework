<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media\View;

use Application\AppFactory;
use Application\MarkdownRenderer;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Tags\AdminScreens\RecordTaggingScreenInterface;
use Application\Tags\AdminScreens\RecordTaggingScreenTrait;
use Application\Tags\Taggables\TaggableInterface;
use Application_Admin_Area_Mode_Submode_CollectionRecord;
use Application_Media_Document;
use AppUtils\FileHelper;
use UI;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property MediaRecord $record
 */
class BaseMediaTagsScreen
    extends Application_Admin_Area_Mode_Submode_CollectionRecord
    implements RecordTaggingScreenInterface
{
    use RecordTaggingScreenTrait;

    public const URL_NAME = 'tagging';
    public const FORM_NAME = 'media_tags';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    protected function createCollection() : MediaCollection
    {
        return AppFactory::createMediaCollection();
    }

    protected function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getNavigationTitle(): string
    {
        return t('Tags');
    }

    public function getTitle(): string
    {
        return t('Tags');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        $this->handleTaggableActions();

        return true;
    }

    public function getTaggableRecord(): TaggableInterface
    {
        return $this->record->getMediaDocument();
    }

    public function getAdminCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }
}
