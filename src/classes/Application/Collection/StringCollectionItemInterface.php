<?php

declare(strict_types=1);

namespace Application\Collection;

interface StringCollectionItemInterface extends CollectionItemInterface
{
    public function getID() : string;
}
