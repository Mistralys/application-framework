<?php

declare(strict_types=1);

namespace Application\API\Traits;

interface JSONResponseWithExampleInterface extends JSONResponseInterface
{
    public function isExampleResponse() : bool;
}
