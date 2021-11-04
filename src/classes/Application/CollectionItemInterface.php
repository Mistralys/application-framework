<?php

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
