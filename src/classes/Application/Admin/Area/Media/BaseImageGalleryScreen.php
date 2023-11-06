<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaFilterCriteria;
use Application\Media\Collection\MediaFilterSettings;
use Application\Media\Collection\MediaRecord;
use Application_Admin_Area_Mode;
use Application_Media_Document_Image;
use AppUtils\ClassHelper;
use UI;

class BaseImageGalleryScreen extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'image-gallery';
    private MediaCollection $media;
    private MediaFilterCriteria $criteria;
    private MediaFilterSettings $filterSettings;
    private bool $hasItems;

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
        $this->hasItems = $this->criteria->countItems() > 0;

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
            ->makeLinked($this->media->getAdminImageGalleryURL());
    }

    protected function _renderContent()
    {
        $this->ui->addStylesheet('media-image-gallery.css');

        $this->filterSettings->configureFilters($this->criteria);

        $items = $this->criteria->getItemsObjects();

        $this->renderer->appendContent('<div class="image-gallery">');

        foreach($items as $item) {
            $this->renderer->appendContent(
                $this->renderImageThumbnail($item)
            );
        }

        $this->renderer->appendContent('</div>');

        return $this->renderer
            ->setWithSidebar($this->hasItems);
    }

    private function renderImageThumbnail(MediaRecord $item) : string
    {
        $template = <<<'HTML'
<div class="gallery-card">
    <div class="gallery-image">%1$s</div>
    <div class="gallery-details">
        %2$s
    </div>
</div>
HTML;

        $document = ClassHelper::requireObjectInstanceOf(Application_Media_Document_Image::class, $item->getMediaDocument());

        return sprintf(
            $template,
            $item->renderThumbnail(260),
            sb()
                ->bold($item->getLabel())
                ->nl()
                ->add($document->getExtension())
                ->add('|')
                ->add($document->getDimensions()->toReadableString())
                ->add('|')
                ->add($document->getFilesizeReadable())
        );
    }
}
