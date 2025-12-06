<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode;

use Application\Interfaces\AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode;

abstract class BaseSubmode extends Application_Admin_Area_Mode_Submode implements AllowableMigrationInterface
{
    use AllowableMigrationTrait;
}
