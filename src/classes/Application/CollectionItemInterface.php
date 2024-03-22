<?php

declare(strict_types=1);

interface Application_CollectionItemInterface
{
    /**
     * @return int
     */
    public function getID() : int;

    /**
     * @return string
     */
    public function getLabel() : string;
}
