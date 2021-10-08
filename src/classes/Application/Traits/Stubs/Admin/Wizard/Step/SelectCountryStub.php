<?php

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_SelectCountryStep
 * @see Application_Interfaces_Admin_Wizard_SelectCountryStep
 */
class Application_Traits_Stubs_Admin_Wizard_SelectCountryStub
    extends Application_Admin_Wizard_Step
    implements Application_Interfaces_Admin_Wizard_SelectCountryStep
{
    use Application_Traits_Admin_Wizard_SelectCountryStep;

    public function isMode() : bool
    {
        return false;
    }

    public function isSubmode() : bool
    {
        return false;
    }

    public function isAction() : bool
    {
        return false;
    }

    public function getAbstract() : string
    {
        return '';
    }

    public function isInvariantSelectable() : bool
    {
        return false;
    }

    public function initDone()
    {
    }

    protected function init()
    {
    }

    protected function preProcess()
    {
    }
}
