<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;

interface ViewSubmodeInterface extends AdminSubmodeInterface, ClassLoaderScreenInterface
{

}
