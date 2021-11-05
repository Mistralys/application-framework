<?php
/**
 * File containing the {@link UI_Page_StepsNavigator} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_StepsNavigator
 */

/**
 * The class for individual steps in the navigator
 * @see UI_Page_StepsNavigator_Step
 */
require_once 'UI/Page/StepsNavigator/Step.php';

/**
 * Helper class used to generate the HTML markup for displaying a
 * wizard's step by step navigation.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator
{
    public const ERROR_NO_STEPS_TO_SELECT = 556001;
    
    public const ERROR_UNKNOWN_STEP = 556002;
    
   /**
    * @var UI_Page
    */
    protected $page;
    
    public function __construct(UI_Page $page)
    {
        $this->page = $page;
    }
    
   /**
    * @return UI_Page
    */
    public function getPage()
    {
        return $this->page;
    }
    
   /**
    * @var UI_Page_StepsNavigator_Step[] 
    */
    protected $steps = array();
    
   /**
    * Adds a step to the navigator.
    * @param string $name
    * @param string $label
    * @return UI_Page_StepsNavigator_Step
    */
    public function addStep($name, $label)
    {
        $number = count($this->steps) + 1;
        $step = new UI_Page_StepsNavigator_Step($this, $number, $name, $label);
        $this->steps[$name] = $step;
        return $step;
    }
    
    protected $selectedName;
    
   /**
    * Selects the step that should be marked as active in the
    * navigator.
    * 
    * @param string $name
    * @throws Application_Exception
    */
    public function selectStep($name)
    {
        if(!isset($this->steps[$name])) {
            throw new Application_Exception(
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
    }
    
   /**
    * Retrieves the name of the step currently selected (marked
    * as active) in the navigator. If none has been specifically
    * selected, this will be the first in the list.
    * 
    * @throws Application_Exception
    * @return string
    */
    public function getSelectedName()
    {
        if(!isset($this->selectedName)) {
            if(empty($this->steps)) {
                throw new Application_Exception(
                    'No steps to select',
                    'Cannot get the name of the selected step: no steps have been added.',
                    self::ERROR_NO_STEPS_TO_SELECT
                );
            }
            $this->selectedName = key($this->steps);
        }
        
        return $this->selectedName;
    }
    
   /**
    * Retrieves the currently selected (marked as active) step
    * object instance. If none has been specifically selected, 
    * this will be the first in the list.
    *  
    * @return UI_Page_StepsNavigator_Step
    */
    public function getSelectedStep()
    {
        return $this->steps[$this->getSelectedName()];
    }
    
    public function render()
    {
        if(empty($this->steps)) {
            return '';
        }
        
        $classes = array('steps-navigator');
        if($this->options['numbered']) {
            $classes[] = 'numbered';
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
    
    protected $options = array(
        'numbered' => false
    );
    
   /**
    * Adds numbers to each step.
    * @return UI_Page_StepsNavigator
    */
    public function makeNumbered()
    {
        return $this->setOption('numbered', true);
    }
    
   /**
    * Sets a navigator option.
    * 
    * @param string $name
    * @param mixed $value
    * @return UI_Page_StepsNavigator
    */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }
    
    public function getOption($name, $default=null)
    {
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        
        return $default;
    }
}