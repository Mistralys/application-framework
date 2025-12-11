<?php

declare(strict_types=1);

namespace Application\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminScreenInterface;

interface DevelModeInterface extends AdminScreenInterface, ClassLoaderScreenInterface
{
    public function getDevCategory() : string;
}
