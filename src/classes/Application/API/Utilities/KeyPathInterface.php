<?php

declare(strict_types=1);

namespace Application\API\Utilities;

use AppUtils\Interfaces\StringableInterface;

interface KeyPathInterface extends StringableInterface
{
    public function getPath() : string;
}
