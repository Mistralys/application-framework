<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminModeInterface;

interface ClientModeInterface extends AdminModeInterface, ClassLoaderScreenInterface
{

}
