<?php

declare(strict_types=1);

namespace Application\Admin\Wizard;

use Application\Interfaces\AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait;

abstract class BaseWizardMode extends \Application_Admin_Wizard implements AllowableMigrationInterface
{
    use AllowableMigrationTrait;
}
