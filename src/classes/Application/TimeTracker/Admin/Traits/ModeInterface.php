<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminModeInterface;

interface ModeInterface extends AdminModeInterface, ClassLoaderScreenInterface
{

}
