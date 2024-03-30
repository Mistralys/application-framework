<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media\View;

use Application\AppFactory;
use Application\MarkdownRenderer;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application_Admin_Area_Mode_Submode_CollectionRecord;
use Application_Media_Document;
use AppUtils\FileHelper;
use UI;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property MediaRecord $record
 */
abstract class BaseMediaStatusScreen extends Application_Admin_Area_Mode_Submode_CollectionRecord
{
    public const URL_NAME = 'status';
    public const REQUEST_PARAM_DOWNLOAD = 'download';
    public const TUMBNAIL_SIZE = 460;
    private Application_Media_Document $document;

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
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    public function getDefaultAction(): string
    {
        return '';
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
        $this->record->renderThumbnail(self::TUMBNAIL_SIZE);

        // Get a thumbnail size adapted to the document type.
        $imageSize = $this->document->getThumbnailDefaultSize(self::TUMBNAIL_SIZE);

        $grid->addHeader(t('Preview'));
        $grid->addMerged(
            sb()->link(
                '<img src="'.$this->document->getThumbnailURL($imageSize).'" width="'.$imageSize.'" alt="">',
                $this->document->getThumbnailURL()
            )
        );
    }
}
