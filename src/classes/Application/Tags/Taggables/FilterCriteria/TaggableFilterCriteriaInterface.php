<?php

declare(strict_types=1);

namespace Application\Tags\Taggables\FilterCriteria;

use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\TagRecord;

interface TaggableFilterCriteriaInterface
{
    public const FILTER_TAGGABLE_TAG_IDS = 'taggable_tag_ids';

    /**
     * @param TagRecord $tag
     * @return $this
     */
    public function selectTag(TagRecord $tag) : self;

    /**
     * @param TagRecord[] $tags
     * @return $this
     */
    public function selectTags(array $tags) : self;

    public function getTagContainer(): TagCollectionInterface;
}
