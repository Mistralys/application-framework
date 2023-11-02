<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media\View;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application_Admin_Area_Mode_Submode_CollectionRecord;
use AppUtils\FileHelper;
use UI;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property MediaRecord $record
 */
abstract class BaseMediaStatusScreen extends Application_Admin_Area_Mode_Submode_CollectionRecord
{
    public const URL_NAME = 'status';
    public const REQUEST_PARAM_DOWNLOAD = 'download';

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
        if($this->request->getBool(self::REQUEST_PARAM_DOWNLOAD)) {
            $this->record->sendFile(true);
        }

        return true;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('download-file', t('Download file'))
            ->setIcon(UI::icon()->download())
            ->link($this->record->getAdminDownloadURL())
            ->setTooltip(t('Downloads the original media file.'));
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $grid = $this->ui->createPropertiesGrid();

        $document = $this->record->getMediaDocument();

        $grid->add(t('ID'), sb()->codeCopy($this->record->getID()));
        $grid->add(t('Type'), sb()->icon($document->getTypeIcon())->add($document->getTypeLabel()));
        $grid->add(t('Label'), $this->record->getLabel());
        $grid->add(t('Added by'), $this->record->getAuthor()->getName());
        $grid->addDate(t('Date added'), $this->record->getDateAdded())
            ->withTime()
            ->withDiff();
        $grid->addByteSize(t('File size'), $document->getFilesize());

        $grid->addHeader(t('Preview'));
        $grid->addMerged(
            sb()->link(
                '<img src="'.$document->getThumbnailURL(460).'">',
                $document->getThumbnailURL()
            )
        );

        if($this->user->isDeveloper()) {
            $grid->addHeader(t('Developer info'));
            $grid->add(t('Path on disk'), sb()->code(FileHelper::relativizePath($document->getPath(), APP_ROOT)));
        }

        return $this->renderer
            ->makeWithSidebar()
            ->appendContent($grid);
    }
}
