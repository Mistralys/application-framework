<?php

declare(strict_types=1);

namespace Application\Admin\Area;

use Application\Interfaces\AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode;

abstract class BaseMode extends Application_Admin_Area_Mode implements AllowableMigrationInterface
{
    use AllowableMigrationTrait;
}
