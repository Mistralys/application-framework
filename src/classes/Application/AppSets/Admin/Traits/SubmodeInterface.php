<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;

interface SubmodeInterface extends AdminSubmodeInterface, ClassLoaderScreenInterface
{

}
