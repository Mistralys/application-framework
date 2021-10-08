<?php
/**
 * File containing the interface {@see Application_Interfaces_Admin_Wizard_WithConfirmationStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Interfaces_Admin_Wizard_WithConfirmationStep
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_WithConfirmationStep
 * @see Application_Traits_Stubs_Admin_Wizard_WithConfirmationStepStub
 */
interface Application_Interfaces_Admin_Wizard_WithConfirmationStep
{
    public function getStepConfirmation() : Application_Interfaces_Admin_Wizard_Step_Confirmation;

    /**
     * Retrieves the reference ID created in the confirmation step.
     * Requires the confirmation to have been processed.
     *
     * @return string
     *
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws Application_Exception
     * @see Application_Interfaces_Admin_Wizard_Step_Confirmation::ERROR_NO_REFERENCE_ID_SET
     */
    public function getSelectedReferenceID() : string;
}
