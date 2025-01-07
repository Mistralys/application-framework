<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaFilterCriteria;
use Application\Media\Collection\MediaFilterSettings;
use Application\Media\Collection\MediaRecord;
use Application\Media\MediaException;
use Application_Admin_Area_Mode;
use Application_Media_Document_Image;
use AppUtils\AttributeCollection;
use AppUtils\ClassHelper;
use AppUtils\PaginationHelper;
use UI;
use UI\PaginationRenderer;

class BaseImageGalleryScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'image-gallery';
    public const PREFERRED_THUMBNAIL_SIZE = 260;
    public const REQUEST_PARAM_PAGE_NUMBER = 'page-number';
    public const STYLESHEET_FILE = 'media-image-gallery.css';
    private MediaCollection $media;
    private MediaFilterCriteria $criteria;
    private MediaFilterSettings $filterSettings;
    private bool $hasItems;
    private PaginationRenderer $paginator;
    private int $totalCount;
    private int $maxPages;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewMedia();
    }

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Image gallery');
    }

    public function getTitle(): string
    {
        return t('Image gallery');
    }

    protected function _handleActions(): bool
    {
        $this->media = AppFactory::createMediaCollection();
        $this->criteria = $this->media->getFilterCriteria();
        $this->filterSettings = $this->media->getFilterSettings()->configureForImageGallery();
        $this->totalCount = $this->criteria->countItems();
        $this->maxPages = (int)ceil($this->totalCount / self::IMAGES_PER_PAGE);
        $this->hasItems = $this->totalCount > 0;

        $this->paginator = $this->ui->createPagination(
            new PaginationHelper($this->totalCount, self::IMAGES_PER_PAGE, $this->getCurrentPage()),
            self::REQUEST_PARAM_PAGE_NUMBER,
            $this->getURL()
        )
            ->setAdjacentPages(5);

        return true;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->image());
    }

    protected function _handleSidebar(): void
    {
        if($this->hasItems) {
            $this->sidebar->addFilterSettings($this->filterSettings);
        }
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->media->adminURL()->gallery());
    }

    public function getCurrentPage() : int
    {
        $page = (int)$this->request->getParam(self::REQUEST_PARAM_PAGE_NUMBER, 1);

        if($page < 1) {
            return 1;
        }

        if($page > $this->maxPages) {
            return $this->maxPages;
        }

        return $page;
    }

    public const IMAGES_PER_PAGE = 20;

    protected function _renderContent()
    {
        $this->ui->addStylesheet(self::STYLESHEET_FILE);

        $this->filterSettings->configureFilters($this->criteria);
        $this->paginator->configureFilters($this->criteria);

        $items = $this->criteria->getItemsObjects();
        $pagination = '<div class="gallery-pagination">'.$this->paginator->render().'</div>';

        $this->renderer->appendContent($pagination);
        $this->renderer->appendContent('<div class="image-gallery">');

        foreach($items as $item) {
            $this->renderer->appendContent(
                $this->renderImageThumbnail($item)
            );
        }

        $this->renderer->appendContent('</div>');
        $this->renderer->appendContent($pagination);

        return $this->renderer
            ->setWithSidebar($this->hasItems);
    }

    private function renderImageThumbnail(MediaRecord $record) : string
    {
        $template = <<<'HTML'
<div class="gallery-card">
    <div class="gallery-image">%1$s</div>
    <div class="gallery-details">
        %2$s
    </div>
    %3$s
</div>
HTML;

        try {
            $image = ClassHelper::requireObjectInstanceOf(Application_Media_Document_Image::class, $record->getMediaDocument());

            return sprintf(
                $template,
                $record->renderThumbnail(self::PREFERRED_THUMBNAIL_SIZE),
                $this->renderMetaInfo($record, $image),
                $this->renderTagEditor($image)
            );
        }
        catch (MediaException $e)
        {
            $e->disableLogging();

            return sprintf(
                $template,
                '<img src="'.$this->ui->getTheme()->getEmptyImageURL().'" width="'.self::PREFERRED_THUMBNAIL_SIZE.'" alt=""/>',
                sb()
                    ->bold($record->getLabel())
                    ->nl()
                    ->warning(sb()->bold(t('Not found in the storage.'))),
                ''
            );
        }
    }

    private function renderTagEditor(Application_Media_Document_Image $image) : string
    {
        if($image->isTaggingEnabled()) {
            return $image->getTagManager()->renderTaggingUI();
        }

        return '';
    }

    private function renderMetaInfo(MediaRecord $record, Application_Media_Document_Image $image) : string
    {
        return (string)sb()
            ->bold($record->getLabelLinked())
            ->nl()
            ->add($image->getExtension())
            ->add('|')
            ->add($image->getDimensions()->toReadableString())
            ->add('|')
            ->add($image->getFilesizeReadable());
    }
}
