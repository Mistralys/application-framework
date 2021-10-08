<?php
/**
 * File containing the interface {@see Application_Interfaces_Admin_Wizard_Step_Confirmation}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Interfaces_Admin_Wizard_Step_Confirmation
 */

declare(strict_types=1);

/**
 * Step in a wizard: Confirm the wizard with a summary screen.
 *
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_Step_Confirmation
 */
interface Application_Interfaces_Admin_Wizard_Step_Confirmation
{
    const STEP_NAME = 'Confirm';
    const FORM_NAME = 'wizard_confirmation';
    const PARAM_REFERENCE_ID = 'reference_id';
    const ERROR_NO_REFERENCE_ID_SET = 95001;

    public function getReferenceID() : string;

    public function requireReferenceID() : string;
}
