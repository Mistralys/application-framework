<?php

declare(strict_types=1);

namespace Application\API\Groups;

interface APIGroupInterface
{
    public function getID() : string;
    public function getLabel() : string;
    public function getDescription() : string;
}
