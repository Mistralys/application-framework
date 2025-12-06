<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use AppUtils\Interfaces\StringableInterface;

class StringableStub implements StringableInterface
{
    public const string RETURN_VALUE = 'I am stringable';

    public function __toString() : string
    {
        return self::RETURN_VALUE;
    }
}
