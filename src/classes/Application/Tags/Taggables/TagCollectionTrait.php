<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use AppUtils\ClassHelper;

/**
 * @see TagCollectionInterface
 */
trait TagCollectionTrait
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
                $this->getTagTable(),
                $this->getTagPrimary()
            )
        );

        $this->tagContainer = $container;

        return $container;
    }
}
