<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application\AppFactory;
use Application\Tags\Interfaces\TagFilterSettingsInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\Traits\TagFilterSettingsTrait;
use Application_Media_Document_Image;
use Closure;
use DBHelper_BaseFilterSettings;

/**
 * @property MediaFilterCriteria $filters
 * @property MediaCollection $collection
 *
 */
class MediaFilterSettings extends DBHelper_BaseFilterSettings implements TagFilterSettingsInterface
{
    use TagFilterSettingsTrait;

    public const string SETTING_SEARCH = 'search';
    public const string SETTING_EXTENSIONS = 'extensions';
    public const string SETTING_TAGS = 'tags';

    private bool $isImageGallery = false;

    public function getTagCollection(): TagCollectionInterface
    {
        return $this->collection;
    }

    public function configureForImageGallery() : self
    {
        $this->isImageGallery = true;
        $this->setID('media-image-gallery');
        return $this;
    }

    protected function registerSettings(): void
    {
        $this->registerSearchSetting(self::SETTING_SEARCH);

        $this->registerSetting(self::SETTING_EXTENSIONS, t('File extensions'))
            ->setInjectCallback(Closure::fromCallable(array($this, 'inject_extensions')))
            ->setConfigureCallback(Closure::fromCallable(array($this, 'configure_extensions')));

        $this->registerTagSetting(self::SETTING_TAGS);
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
    }

    protected function configure_extensions() : void
    {
        $extensions = (array)$this->getSetting(self::SETTING_EXTENSIONS);
        if(empty($extensions) && $this->isImageGallery)
        {
            $extensions = Application_Media_Document_Image::IMAGE_EXTENSIONS;
        }

        $this->filters->selectExtensions($extensions);
    }

    public function getTaggableFilters(): TaggableFilterCriteriaInterface
    {
        return $this->filters;
    }
}
