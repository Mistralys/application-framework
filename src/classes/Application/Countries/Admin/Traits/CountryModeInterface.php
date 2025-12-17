<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\Interfaces\Admin\AdminModeInterface;

interface CountryModeInterface extends AdminModeInterface, ClassLoaderScreenInterface
{

}