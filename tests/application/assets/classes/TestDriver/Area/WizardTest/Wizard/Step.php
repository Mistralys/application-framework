<?php

declare(strict_types=1);


abstract class TestDriver_Area_WizardTest_Wizard_Step extends \Application_Admin_Wizard_Step
{
    protected function init() : void
    {

        $this->_init();
    }

    protected function _init() : void
    {

    }

    public function isMode() : bool
    {
        return false;
    }

    public function isSubmode() : bool
    {
        return true;
    }

    public function isAction() : bool
    {
        return false;
    }
}