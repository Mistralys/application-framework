<?php

declare(strict_types=1);

namespace Application\Tags\Interfaces;

use Application\FilterSettings\SettingDef;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\TagRecord;
use Application\FilterSettings\FilterSettingsInterface;

interface TagFilterSettingsInterface extends FilterSettingsInterface
{
    public const DEFAULT_SETTING = 'tags';

    public function getTagCollection() : TagCollectionInterface;

    public function getTagSetting() : ?SettingDef;

    /**
     * @return TagRecord[]
     */
    public function getSelectedTags() : array;

    /**
     * @return int[]
     */
    public function getSelectedTagIDs() : array;

    public function getTaggableFilters() : TaggableFilterCriteriaInterface;
}
