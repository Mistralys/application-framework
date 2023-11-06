<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application\AppFactory;
use Application_Media_Document_Image;
use DBHelper_BaseFilterSettings;

/**
 * @property MediaFilterCriteria $filters
 */
class MediaFilterSettings extends DBHelper_BaseFilterSettings
{
    public const SETTING_SEARCH = 'search';
    public const SETTING_EXTENSIONS = 'extensions';
    private bool $isImageGallery = false;

    public function configureForImageGallery() : self
    {
        $this->isImageGallery = true;
        $this->setID('media-image-gallery');
        return $this;
    }

    protected function registerSettings(): void
    {
        $this->registerSetting(self::SETTING_SEARCH, t('Search'));
        $this->registerSetting(self::SETTING_EXTENSIONS, t('File extensions'));
    }

    protected function inject_extensions() : void
    {
        $el = $this->addMultiselect(self::SETTING_EXTENSIONS);
        $el->makeMultiple();
        $el->enableFiltering();
        $el->enableSelectAll();
        $el->makeBlock();

        if($this->isImageGallery)
        {
            $extensions = Application_Media_Document_Image::IMAGE_EXTENSIONS;
        }
        else
        {
            $extensions = AppFactory::createMedia()->getExtensions();
        }

        foreach ($extensions as $extension) {
            $el->addOption($extension, $extension);
        }
    }

    protected function _configureFilters(): void
    {
        $this->filters->setSearch($this->getSetting(self::SETTING_SEARCH));

        $extensions = (array)$this->getSetting(self::SETTING_EXTENSIONS);
        if(empty($extensions) && $this->isImageGallery)
        {
            $extensions = Application_Media_Document_Image::IMAGE_EXTENSIONS;
        }

        $this->filters->selectExtensions($extensions);
    }
}
