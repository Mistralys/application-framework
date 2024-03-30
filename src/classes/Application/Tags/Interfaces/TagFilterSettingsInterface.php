<?php

declare(strict_types=1);

namespace Application\Tags\Interfaces;

use Application\FilterSettings\SettingDef;
use Application\FilterSettingsInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\TagCollectionInterface;

interface TagFilterSettingsInterface extends FilterSettingsInterface
{
    public const DEFAULT_SETTING = 'tags';

    public function getTagCollection() : TagCollectionInterface;

    public function getTagSetting() : ?SettingDef;

    public function getSelectedTags() : array;

    /**
     * @return int[]
     */
    public function getSelectedTagIDs() : array;

    public function getTaggableFilters() : TaggableFilterCriteriaInterface;
}
