<?php

declare(strict_types=1);

namespace Application\Admin;

use Application\Interfaces\AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area;

abstract class BaseArea extends Application_Admin_Area implements AllowableMigrationInterface
{
    use AllowableMigrationTrait;
}
