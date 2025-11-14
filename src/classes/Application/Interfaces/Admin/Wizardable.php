<?php

use Application\Interfaces\Admin\AdminScreenInterface;
use UI\AdminURLs\AdminURLInterface;

interface Application_Interfaces_Admin_Wizardable extends AdminScreenInterface
{
    public const ADMIN_WIZARD_ERROR_UNKNOWN_STEP = 557001;
    public const ADMIN_WIZARD_ERROR_STEP_ALREADY_EXISTS = 557002;

    /**
     * @return Application_Admin_Wizard_Step
     */
    public function getActiveStep() : Application_Admin_Wizard_Step;

    /**
     * The URL to redirect the user to if they cancel the wizard.
     * @return string
     */
    public function getCancelURL() : string;

    /**
     * Retrieves the base class name for the wizard. This determines
     * the class names for the step classes, in the following form:
     * <code>ClassBase_Step_StepName</code>.
     *
     * @return string
     */
    public function getClassBase() : string;

    /**
     * @return Application_Admin_Wizard_Step|NULL
     */
    public function getNextIncompleteStep() : ?Application_Admin_Wizard_Step;

    /**
     * @return Application_Admin_Wizard_Step|NULL
     */
    public function getNextStep() : ?Application_Admin_Wizard_Step;

    /**
     * @return Application_Admin_Wizard_Step|NULL
     */
    public function getPreviousStep() : ?Application_Admin_Wizard_Step;

    public function getSessionID() : string;

    /**
     * @return Application_Admin_Wizard_Step
     */
    public function getStep(string $name) : Application_Admin_Wizard_Step;

    /**
     * @return string
     */
    public function getSuccessMessage() : string;

    public function initWizard() : void;

    /**
     * Returns the URL to send the user too once the
     * wizard has been successfully completed.
     *
     * @return string
     */
    public function getSuccessURL() : string;

    /**
     * The ID of the wizard: this is an arbitrary string that
     * is used to namespace settings and the like. Convention
     * is to use a camel case string, e.g. <code>WizardName</code>.
     * Ideally, this should be unique within the whole application.
     *
     * @return string
     */
    public function getWizardID() : string;

    /**
     * Handles any tasks to be done if the specified step has
     * been invalidated for any reason.
     *
     * @param Application_Admin_Wizard_Step $step
     * @param string $reasonMessage
     * @param int $callingStep
     */
    public function handle_stepInvalidated(Application_Admin_Wizard_Step $step, string $reasonMessage, int $callingStep = 0) : void;

    /**
     * Handles any tasks to be done when a step's data has
     * been updated.
     *
     * @param Application_Admin_Wizard_Step $step
     */
    public function handle_stepUpdated(Application_Admin_Wizard_Step $step) : void;

    /**
     * Checks whether the step exists in the wizard.
     *
     * @param string $name
     * @return bool
     */
    public function hasStep(string $name) : bool;

    /**
     * Checks whether the specified step has been completed.
     *
     * @param string $name
     * @return bool
     */
    public function isStepComplete(string $name) : bool;

    /**
     * Whether the current page is in simulation mode.
     * @return bool
     */
    public function isSimulationEnabled() : bool;

    /**
     * Checks whether the step is valid and can be jumped to.
     * @param string $name
     * @return boolean
     */
    public function isValidStep(string $name) : bool;

    public function processCancelWizard() : void;

    public function processCompleteWizard() : void;

    public function reset() : void;

    public function stepExists(string $name) : bool;

    /**
     * Returns the URL to which the user should be sent
     * if they click the cancel button.
     *
     * @return string|AdminURLInterface
     */
    public function getCanceledURL() : string|AdminURLInterface;
}
