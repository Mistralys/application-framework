<?php
/**
 * File containing the trait {@see Application_Traits_Admin_Wizard_WithConfirmationStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Traits_Admin_Wizard_WithConfirmationStep
 */

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;

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
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function getStepConfirmation() : Application_Interfaces_Admin_Wizard_Step_Confirmation
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Interfaces_Admin_Wizard_Step_Confirmation::class,
            $this->getStep(Application_Interfaces_Admin_Wizard_Step_Confirmation::STEP_NAME)
        );
    }

    /**
     * Retrieves the reference ID that was created by the
     * confirmation step.
     *
     * @return string
     *
     * @throws Application_Exception
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     *
     * @see Application_Interfaces_Admin_Wizard_Step_Confirmation::ERROR_NO_REFERENCE_ID_SET
     */
    public function getSelectedReferenceID() : string
    {
        return $this->getStepConfirmation()->requireReferenceID();
    }
}
