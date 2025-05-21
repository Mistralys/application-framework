<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Create;

use Application\AppFactory;
use Application\Countries\Admin\Screens\BaseCreateScreen;
use Application_Admin_Wizard_Step;
use Application_Countries;

/**
 * @property BaseCreateScreen $wizard
 */
abstract class BaseCreateStep extends Application_Admin_Wizard_Step
{
    protected Application_Countries $countries;

    public function isMode(): bool
    {
        return false;
    }

    public function isSubmode(): bool
    {
        return true;
    }

    public function isAction(): bool
    {
        return false;
    }

    protected function init(): void
    {
    }

    public function initDone(): void
    {
        $this->countries = AppFactory::createCountries();
    }
}
