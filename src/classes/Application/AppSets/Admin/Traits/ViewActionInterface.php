<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminActionInterface;

interface ViewActionInterface extends AdminActionInterface, ClassLoaderScreenInterface
{

}
