<?php

declare(strict_types=1);

namespace Application\Media;

use Application\AppFactory;
use Application\Tags\Taggables\TagContainer;
use Application\Tags\TagRecord;

/**
 */
class MediaTagContainer extends TagContainer
{
    public function getByTag(TagRecord $tag) : array
    {
        $ids = $this->getRecordIDsByTag($tag);
        $result = array();
        $collection = AppFactory::createMedia();

        foreach ($ids as $id) {
            $result[] = $collection->getByID($id);
        }

        return $result;
    }
}
