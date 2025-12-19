<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode\Submode;

use Application\Interfaces\AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_Action;

abstract class BaseAction
    extends Application_Admin_Area_Mode_Submode_Action
    implements AllowableMigrationInterface
{
    use AllowableMigrationTrait;

    public function getDefaultSubscreenClass() : null
    {
        return null;
    }
}
