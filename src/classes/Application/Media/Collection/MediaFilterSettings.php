<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application\AppFactory;
use DBHelper_BaseFilterSettings;

/**
 * @property MediaFilterCriteria $filters
 */
class MediaFilterSettings extends DBHelper_BaseFilterSettings
{
    public const SETTING_SEARCH = 'search';
    public const SETTING_EXTENSIONS = 'extensions';

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

        $extensions = AppFactory::createMedia()->getExtensions();
        foreach($extensions as $extension)
        {
            $el->addOption($extension, $extension);
        }
    }

    protected function _configureFilters(): void
    {
        $this->filters->setSearch($this->getSetting(self::SETTING_SEARCH));

        $this->filters->selectExtensions((array)$this->getSetting(self::SETTING_EXTENSIONS));
    }
}
