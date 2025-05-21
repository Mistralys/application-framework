<?php

declare(strict_types=1);

namespace TestDriver\Admin;

use Application\Interfaces\Admin\AdminScreenInterface;

interface TestingScreenInterface extends AdminScreenInterface
{
    public static function getTestLabel() : string;
}
