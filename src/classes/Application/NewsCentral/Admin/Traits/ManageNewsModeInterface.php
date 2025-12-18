<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminModeInterface;

interface ManageNewsModeInterface extends AdminModeInterface, ClassLoaderScreenInterface
{

}
