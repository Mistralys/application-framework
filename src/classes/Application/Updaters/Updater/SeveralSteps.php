<?php
/**
 * File containing the {@link Application_Updaters_Updater_SeveralSteps} class.
 *
 * @package Application
 * @subpackage Maintenance
 */

/**
 * Base class for updaters that need several steps to process
 * the upgrade: handles moving from step to step automatically.
 * 
 * @package Application
 * @subpackage Maintenance
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Updaters_Updater_SeveralSteps extends Application_Updaters_Updater
{
    const ERROR_MISSING_STEP_METHOD = 67854001;
    
   /**
    * @return string[]
    */
	abstract protected function getSteps() : array;
	
	abstract protected function isUpgradeDone() : bool;
	
   /**
    * @var array<string,array<string,mixed>>
    */
	protected $steps;
	
	protected $stepsIndex = array();
	
   /**
    * @var string
    */
	protected $currentStep;
	
	public function start()
	{
	    // check if the upgrade has been completed
	    if($this->isUpgradeDone()) {
	        $this->cleanUp();
	        return $this->renderPage(
	           $this->renderInfoMessage(
	               t('Congratulations, the upgrade is complete.')        
               )        
            );
	    }
	    
	    $steps = $this->getSteps();
	    $counter = 0;
	    foreach($steps as $step => $label) {
	        $this->steps[$step] = array(
                'label' => $label,
                'idx' => $counter,
                'nr' => $counter+1 
	        );
	        
	        $this->stepsIndex[] = $step;
	        
	        $counter++;
	    } 
	    
	    // register the step names for the request
	    $this->request->registerParam('step')->setEnum(array_keys($this->steps));
	    
	    // default current step is the one from the session,
	    // or the default (first) step.
	    $this->currentStep = $this->getSessionValue('step');
	    if(!isset($this->steps[$this->currentStep])) {
	        $this->currentStep = key($this->steps);
	    }
	    
	    // a specific step has been requested
	    $targetStep = $this->request->getParam('step');
	    if(!empty($targetStep)) {
	        if($this->isStepComplete($targetStep)) {
	            $this->currentStep = $targetStep;
	        } else {
    	        $prevIdx = $this->steps[$targetStep]['idx']-1;
    	        if($prevIdx >= 0 && $this->isStepComplete($this->stepsIndex[$prevIdx])) {
    	            $this->currentStep = $targetStep;
    	        }
	        }
	    }
	    
	    $this->setSessionValue('step', $this->currentStep);
	    
	    $method = 'step_'.$this->currentStep;
	    if(!method_exists($this, $method)) {
	        throw new Application_Exception(
	            'Invalid step',
                sprintf(
                    'The updater [%s] is missing the method [%s] for the [%s] step.',
                    $this->getID(),   
                    $method,
                    $this->currentStep        
                ),
                self::ERROR_MISSING_STEP_METHOD
            );
	    }
	    
		$this->$method();
	}	
	
	protected $hasAlterTablePrivileges;
	
   /**
    * Checks whether the user of the current database connection
    * has ALTER TABLE privileges.
    * 
    * @return boolean
    */
	protected function hasAlterTablePrivileges()
	{
	    if(isset($this->hasAlterTablePrivileges)) {
	        return $this->hasAlterTablePrivileges;
	    }
	    
		$this->hasAlterTablePrivileges = true;
		
		try{
			DBHelper::execute(DBHelper_OperationTypes::TYPE_ALTER, "ALTER TABLE `app_settings` ADD `testfield` INT(11) UNSIGNED NOT NULL");
			DBHelper::execute(DBHelper_OperationTypes::TYPE_ALTER, "ALTER TABLE `app_settings` DROP `testfield`;");
		} catch(Exception $e) {
			$this->hasAlterTablePrivileges = false;	
		}
		
		return $this->hasAlterTablePrivileges;
	}

   /**
    * Retrieves previously saved data for the current step, or the 
    * specified step.
    * 
    * @param string $step
    * @param string $default
    * @return mixed
    */
	protected function getStepData($step=null, $default=null)
	{
	    if(empty($step)) {
	        $step = $this->currentStep;
	    }
	    
	    return $this->getSessionValue('data_'.$step, $default);
	}
	
   /**
    * Saves data for the current step, which can be retrieved
    * again anytime.
    * 
    * @param mixed $data
    * @param string $step
    */
	protected function setStepData($data, $step=null)
	{
	    if(empty($step)) {
	        $step = $this->currentStep;
	    }

	    return $this->setSessionValue('data_'.$step, $data);
	}
	
	protected function renderPage($content, $title=null)
	{
	    // add the step navigation to the content
	    $content = 
	    $this->renderStepAdvancement().
	    $content.
	    $this->renderStepNavigation();
	    
	    return parent::renderPage($content, $title);
	}
	
	protected function renderStepAdvancement()
	{
	    if($this->isUpgradeDone()) {
	        return '';
	    }
	    
	    $html = 
	    '<ul class="nav nav-pills">';
	        $counter = 0;
    	    foreach($this->steps as $step => $def) {
    	        $counter++;
    	        $active = '';
    	        if($step == $this->currentStep) {
    	            $active = ' class="active"';
    	        }
    	        
    	        $html .= 
    	        '<li'.$active.'>'.
    	           '<a href="'.$this->buildURL(array('step' => $step)).'">'.
    	               $counter.'. '.$def['label'].
    	           '</a>'.
	           '</li>';
    	    }
    	    $html .=
	    '</ul>';
    	    
	    return $html;
	}
	
	protected function renderStepNavigation()
	{
	    if($this->isUpgradeDone()) {
	        return '';
	    }
	    
	    $nextStep = null;
	    $prevStep = null;
	    $nextNr = null;
	    $prevNr = null;
	    $steps = array_keys($this->steps);
	    $total = count($steps);

	    for($i=0; $i<$total; $i++) {
	        $step = $steps[$i];
	        if($step == $this->currentStep) {
	            $prevIdx = $i-1;
	            $nextIdx = $i+1;
	            if(isset($steps[$prevIdx])) {
	                $prevStep = $steps[$prevIdx];
	                $prevNr = $prevIdx+1;
	            }
	            if(isset($steps[$nextIdx])) {
	                $nextStep = $steps[$nextIdx];
	                $nextNr = $nextIdx+1;
	            }
	        }
	    }
	    
	    $html = '<hr/>';
	    
	    if($prevStep) {
	        $html .= 
	        '<a href="'.$this->buildURL(array('step' => $prevStep)).'" class="btn btn-default">'.
                UI::icon()->previous().' '.
	            $prevNr.'. '.$this->steps[$prevStep]['label'].
	        '</a>';
	    }

	    if($nextStep) {
	        $html .=
	        '<a href="'.$this->buildURL(array('step' => $nextStep)).'" class="btn btn-primary pull-right">'.
	            UI::icon()->next().' '.
	            $nextNr.'. '.$this->steps[$nextStep]['label'].
	        '</a>';
	    }
	     
	    return $html;
	}
	
	protected function setStepComplete(string $step='') : void
	{
	    if(empty($step)) {
	        $step = $this->currentStep;
	    }
	    
	    $this->setSessionValue('step_'.$step.'_complete', 'true');
	}
	
	protected function isStepComplete(string $step='') : bool
	{
	    if(empty($step)) {
	        $step = $this->currentStep;
	    }

	    return $this->getSessionValue('step_'.$step.'_complete') === 'true';
	}
}