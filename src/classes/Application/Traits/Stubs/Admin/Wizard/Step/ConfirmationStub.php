<?php
/**
 * File containing the class {@see Application_Traits_Stubs_Admin_Wizard_ConfirmationStub}.
 *
 * @package Application
 * @subpackage Stubs
 * @see Application_Traits_Stubs_Admin_Wizard_ConfirmationStub
 */

declare(strict_types=1);

/**
 * Stub for a wizard step: Confirm the wizard with a summary screen.
 *
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_Step_Confirmation
 * @see Application_Interfaces_Admin_Wizard_Step_Confirmation
 */
class Application_Traits_Stubs_Admin_Wizard_ConfirmationStub
    extends Application_Admin_Wizard_Step
    implements Application_Interfaces_Admin_Wizard_Step_Confirmation
{
    use Application_Traits_Admin_Wizard_Step_Confirmation;

    public function getReferenceID() : string
    {
        return '';
    }

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

    public function initDone()
    {
        return '';
    }

    protected function init()
    {
        return '';
    }

    protected function preProcess()
    {
        return '';
    }

    protected function createReferenceID() : string
    {
        return '';
    }

    protected function populateSummaryGrid(UI_PropertiesGrid $grid) : void
    {

    }
}
