<?php

declare(strict_types=1);

namespace Application\Media\Admin\Screens\Mode\View;

use Application\AppFactory;
use Application\MarkdownRenderer;
use Application\Media\Admin\MediaScreenRights;
use Application\Media\Admin\Traits\MediaViewInterface;
use Application\Media\Admin\Traits\MediaViewTrait;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application_Admin_Area_Mode_Submode_CollectionRecord;
use Application_Media_Document;
use AppUtils\FileHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property MediaRecord $record
 */
class StatusSubmode extends BaseRecordSubmode implements MediaViewInterface
{
    use MediaViewTrait;

    public const string URL_NAME = 'status';
    public const string REQUEST_PARAM_DOWNLOAD = 'download';
    public const int THUMBNAIL_SIZE = 460;
    private Application_Media_Document $document;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_VIEW_STATUS;
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    protected function _handleActions(): bool
    {
        $this->document = $this->record->getMediaDocument();

        if($this->request->getBool(self::REQUEST_PARAM_DOWNLOAD)) {
            $this->record->sendFile(true);
        }

        return true;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('download-file', t('Download file'))
            ->setIcon(UI::icon()->download())
            ->link($this->record->adminURL()->download())
            ->setTooltip(t('Downloads the original media file.'));
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $grid = $this->ui->createPropertiesGrid();

        $grid->add(t('ID'), sb()->codeCopy($this->record->getID()));
        $grid->add(t('Type'), sb()->icon($this->document->getTypeIcon())->add($this->document->getTypeLabel()));
        $grid->add(t('Label'), $this->record->getLabel());
        $grid->add(t('Added by'), $this->record->getAuthor()->getName());
        $grid->addDate(t('Date added'), $this->record->getDateAdded())
            ->withTime()
            ->withDiff();
        $grid->addByteSize(t('File size'), $this->document->getFilesize());

        $this->document->injectMetadata($grid);

        $grid->addTags(t('Tags'), $this->record);

        $this->injectDescription($grid);
        $this->injectPreview($grid);

        if($this->user->isDeveloper()) {
            $grid->addHeader(t('Developer info'));
            $grid->add(t('Path on disk'), sb()->code(FileHelper::relativizePath($this->document->getPath(), APP_ROOT)));
        }

        return $this->renderer
            ->makeWithSidebar()
            ->appendContent($grid);
    }

    private function injectDescription(UI_PropertiesGrid $grid) : void
    {
        $descr = $this->record->getDescription();

        if(empty($descr)) {
            return;
        }

        $grid->add(t('Description'), MarkdownRenderer::create()->render($descr));
    }

    private function injectPreview(UI_PropertiesGrid $grid) : void
    {
        $this->record->renderThumbnail(self::THUMBNAIL_SIZE);

        // Get a thumbnail size adapted to the document type.
        $imageSize = $this->document->getThumbnailDefaultSize(self::THUMBNAIL_SIZE);

        $grid->addHeader(t('Preview'));
        $grid->addMerged(
            sb()->link(
                '<img src="'.$this->document->getThumbnailURL($imageSize).'" width="'.$imageSize.'" alt="">',
                $this->document->getThumbnailURL()
            )
        );
    }
}
