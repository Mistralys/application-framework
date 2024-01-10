<?php
/**
 * File containing the {@link UI_Page_StepsNavigator} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_StepsNavigator
 */

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;

/**
 * Helper class used to generate the HTML markup for displaying a
 * wizard's step by step navigation.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const ERROR_NO_STEPS_TO_SELECT = 556001;
    
    public const ERROR_UNKNOWN_STEP = 556002;

    public const OPTION_NUMBERED = 'numbered';

    protected UI_Page $page;
    protected ?string $selectedName = null;
    /**
     * @var UI_Page_StepsNavigator_Step[]
     */
    protected array $steps = array();

    /**
     * @var array<string,mixed>
     */
    protected array $options = array(
        self::OPTION_NUMBERED => false
    );

    public function __construct(?UI_Page $page=null)
    {
        if(is_null($page)) {
            $page = UI::getInstance()->getPage();
        }

        $this->page = $page;
    }
    
    public function getPage() : UI_Page
    {
        return $this->page;
    }

    /**
     * Adds a step to the navigator.
     * @param string $name
     * @param string|number|StringableInterface|NULL $label
     * @return UI_Page_StepsNavigator_Step
     * @throws UI_Exception
     */
    public function addStep(string $name, $label) : UI_Page_StepsNavigator_Step
    {
        $number = count($this->steps) + 1;
        $step = new UI_Page_StepsNavigator_Step($this, $number, $name, $label);
        $this->steps[$name] = $step;
        return $step;
    }
    
   /**
    * Selects the step that should be marked as active in the
    * navigator.
    * 
    * @param string $name
    * @throws UI_Exception
    * @return $this
    */
    public function selectStep(string $name) : self
    {
        if(!isset($this->steps[$name])) {
            throw new UI_Exception(
                'Unknown step name',
                sprintf(
                    'Cannot select step [%s]: it has not been added. Available steps are [%s].',
                    $name,
                    implode(', ', array_keys($this->steps))
                ),
                self::ERROR_UNKNOWN_STEP
            );
        }
        
        $this->selectedName = $name;

        return $this;
    }
    
   /**
    * Retrieves the name of the step currently selected (marked
    * as active) in the navigator. If none has been specifically
    * selected, this will be the first in the list.
    * 
    * @throws UI_Exception
    * @return string
    */
    public function getSelectedName() : string
    {
        if(!isset($this->selectedName)) {
            if(empty($this->steps)) {
                throw new UI_Exception(
                    'No steps to select',
                    'Cannot get the name of the selected step: no steps have been added.',
                    self::ERROR_NO_STEPS_TO_SELECT
                );
            }
            $this->selectedName = key($this->steps);
        }
        
        return $this->selectedName;
    }

    public function isStepSelected(UI_Page_StepsNavigator_Step $step) : bool
    {
        return $this->getSelectedStep()->getName() === $step->getName();
    }

    /**
     * Retrieves the currently selected (marked as active) step
     * object instance. If none has been specifically selected,
     * this will be the first in the list.
     *
     * @return UI_Page_StepsNavigator_Step
     * @throws UI_Exception
     */
    public function getSelectedStep() : UI_Page_StepsNavigator_Step
    {
        $name = $this->getSelectedName();

        if (isset($this->steps[$name])) {
            return $this->steps[$name];
        }

        throw new UI_Exception(
            'Selected step does not exist',
            sprintf(
                'The step [%s] does not exist in the navigator. Available steps are [%s].',
                $name,
                implode(', ', array_keys($this->steps))
            ),
            self::ERROR_NO_STEPS_TO_SELECT
        );
    }

    public function isNumbered() : bool
    {
        return $this->getOption(self::OPTION_NUMBERED) === true;
    }

    public function render() : string
    {
        if(empty($this->steps)) {
            return '';
        }
        
        $classes = array('steps-navigator');
        if($this->isNumbered()) {
            $classes[] = 'numbered';
        } else {
            $classes[] = 'unnumbered';
        }
        
        $html = 
        '<div class="'.implode(' ', $classes).'">'.
            '<ul class="steps-navigator-items">';
                foreach($this->steps as $step) {
                    $html .= $step->render();
                }
                $html .=
            '</ul>'.
        '</div>';
            
        return $html;
    }
    
   /**
    * Adds numbers to each step.
    * @return $this
    */
    public function makeNumbered() : self
    {
        return $this->setOption(self::OPTION_NUMBERED, true);
    }
    
   /**
    * Sets a navigator option.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function setOption(string $name, $value) : self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    public function getOption(string $name, $default=null)
    {
        return $this->options[$name] ?? $default;
    }
}
