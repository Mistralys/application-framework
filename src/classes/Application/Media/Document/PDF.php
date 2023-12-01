<?php

declare(strict_types=1);

use Application\Media\Collection\MediaCollection;

class Application_Media_Document_PDF extends Application_Media_Document
{
    public static function getLabel(): string
    {
        return t('PDF');
    }

    public static function getIcon(): UI_Icon
    {
        return UI::icon()->file();
    }

    public static function getExtensions(): array
    {
        return array(
            'pdf'
        );
    }

    public function getMaxThumbnailSize(): int
    {
        return Application_Media_DocumentInterface::DEFAULT_TYPE_ICON_THUMBNAIL_SIZE;
    }

    public function injectMetadata(UI_PropertiesGrid $grid): void
    {
    }

    public function getThumbnailSourcePath(): string
    {
        return $this->getTypeIconPath();
    }

    public function getMediaSourceID(): string
    {
        return MediaCollection::MEDIA_TYPE;
    }

    public function getMediaPrimaryName(): string
    {
        return MediaCollection::PRIMARY_NAME;
    }
}
