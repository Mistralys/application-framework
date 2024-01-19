<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use AppUtils\ClassHelper;

trait TagContainerTrait
{
    private ?TagContainer $tagContainer = null;

    public function getTagContainer() : TagContainer
    {
        if(isset($this->tagContainer)) {
            return $this->tagContainer;
        }

        $class = $this->getTagContainerClass();
        if($class === null) {
            $class = TagContainer::class;
        }

        $container = ClassHelper::requireObjectInstanceOf(
            TagContainer::class,
            new $class(
                $this->getTaggingTableName(),
                $this->getTaggingPrimaryName()
            )
        );

        $this->tagContainer = $container;

        return $container;
    }
}
