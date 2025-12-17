<?php
/**
 * @package Media
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\Media\Admin\Screens\Mode\View;

use Application\Media\Admin\MediaScreenRights;
use Application\Media\Admin\Traits\MediaViewInterface;
use Application\Media\Admin\Traits\MediaViewTrait;
use Application\Media\Collection\MediaRecord;
use Application\Tags\Admin\Traits\RecordTaggingScreenInterface;
use Application\Tags\Admin\Traits\RecordTaggingScreenTrait;
use Application\Tags\Taggables\TaggableInterface;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use UI\AdminURLs\AdminURLInterface;

/**
 * @package Media
 * @subpackage Admin Screens
 * @property MediaRecord $record
 */
class TagsSubmode
    extends BaseRecordSubmode
    implements
    RecordTaggingScreenInterface,
    MediaViewInterface
{
    use RecordTaggingScreenTrait;
    use MediaViewTrait;

    public const string URL_NAME = 'tagging';
    public const string FORM_NAME = 'media_tags';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_VIEW_TAGS;
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getNavigationTitle(): string
    {
        return t('Tags');
    }

    public function getTitle(): string
    {
        return t('Tags');
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
