<?php
/**
 * File containing the trait {@see Application_Traits_Admin_Wizard_WithConfirmationStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Traits_Admin_Wizard_WithConfirmationStep
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Admin_Wizard_WithConfirmationStep
 * @see Application_Traits_Stubs_Admin_Wizard_WithConfirmationStepStub
 */
trait Application_Traits_Admin_Wizard_WithConfirmationStep
{
    protected function addStepConfirmation() : Application_Admin_Wizard_Step 
    {
        return $this->addStep(Application_Interfaces_Admin_Wizard_Step_Confirmation::STEP_NAME);
    }

    /**
     * @return Application_Interfaces_Admin_Wizard_Step_Confirmation
     *
     * @throws Application_Exception
     * @throws Application_Exception_UnexpectedInstanceType
     */
    public function getStepConfirmation() : Application_Interfaces_Admin_Wizard_Step_Confirmation
    {
        $step = $this->getStep(Application_Interfaces_Admin_Wizard_Step_Confirmation::STEP_NAME);

        if($step instanceof Application_Interfaces_Admin_Wizard_Step_Confirmation)
        {
            return $step;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Interfaces_Admin_Wizard_Step_Confirmation::class, $step);
    }

    /**
     * Retrieves the reference ID that was created by the
     * confirmation step.
     *
     * @return string
     * @throws Application_Exception_UnexpectedInstanceType
     *
     * @throws Application_Exception
     * @see Application_Interfaces_Admin_Wizard_Step_Confirmation::ERROR_NO_REFERENCE_ID_SET
     */
    public function getSelectedReferenceID() : string
    {
        return $this->getStepConfirmation()->requireReferenceID();
    }
}
