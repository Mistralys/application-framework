<?php

declare(strict_types=1);

namespace Application\API\Traits;

trait RequestRequestTrait
{
    public function getRequestMime() : string
    {
        return 'application/x-www-form-urlencoded';
    }
}
