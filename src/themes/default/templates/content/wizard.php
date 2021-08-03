<?php
/**
 * File containing the template class {@see template_default_content_wizard}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_content_wizard
 */

declare(strict_types=1);

/**
 * Renders the wizard UI with the steps navigation, and the
 * currently active step's UI.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard::_renderContent()
 */
class template_default_content_wizard extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $this->ui->addStylesheet('ui-wizard.css');

        $nav = $this->page->createStepsNavigator();
        $nav->makeNumbered();

        foreach ($this->steps as $step)
        {
            $stepID = $step->getID();

            if ($step->isComplete())
            {
                $url = $step->getURLReview();
            }
            else
            {
                $url = $step->getURL();
            }

            $nav->addStep($stepID, $this->renderLabel($step))
                ->link($url)
                ->setEnabled($this->wizard->isValidStep($stepID));
        }

        $nav->selectStep($this->activeStep->getID());

        echo $nav->render();
        echo $this->activeStep->render();
    }

    /**
     * @var Application_Admin_Wizard_Step
     */
    private $activeStep;

    /**
     * @var Application_Admin_Wizard_Step[]
     */
    private $steps;

    /**
     * @var Application_Admin_Wizard
     */
    private $wizard;

    protected function preRender() : void
    {
        $this->wizard = $this->getWizard();
        $this->steps = $this->wizard->getSteps();
        $this->activeStep = $this->wizard->getActiveStep();
    }

    private function getWizard() : Application_Admin_Wizard
    {
        $wizard = $this->getVar('wizard');

        if($wizard instanceof Application_Admin_Wizard) {
            return $wizard;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Admin_Wizard::class, $wizard);
    }

    private function renderLabel(Application_Admin_Wizard_Step $step) : string
    {
        $label = $step->getLabel();
        $icon = $step->getIcon();

        if($icon !== null) {
            $label = $icon.' '.$label;
        }

        return $label;
    }
}
