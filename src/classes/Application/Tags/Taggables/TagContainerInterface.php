<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

interface TagContainerInterface
{
    /**
     * @return class-string|NULL
     */
    public function getTagContainerClass() : ?string;

    public function getTagContainer() : TagContainer;

    public function getTaggingPrimaryName() : string;

    public function getTaggingTableName() : string;
}
