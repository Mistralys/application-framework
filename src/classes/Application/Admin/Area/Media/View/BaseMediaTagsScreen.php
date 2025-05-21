<?php
/**
 * @package Media
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\Admin\Area\Media\View;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Tags\AdminScreens\RecordTaggingScreenInterface;
use Application\Tags\AdminScreens\RecordTaggingScreenTrait;
use Application\Tags\Taggables\TaggableInterface;
use Application_Admin_Area_Mode_Submode_CollectionRecord;

/**
 * @package Media
 * @subpackage Admin Screens
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

    public function getRecordMissingURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
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

    protected function _handleHelp(): void
    {
        $this->renderer
            ->setAbstract(sb()
                ->t('This allows you to select the tags relevant for this media file.')
                ->t('Tip:')
                ->t('It can make it easier to find media files if they are correctly tagged.')
            );
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
        return (string)$this->createCollection()->adminURL()->list();
    }
}
