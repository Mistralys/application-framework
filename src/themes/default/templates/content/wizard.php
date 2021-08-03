<?php

    /* @var $this UI_Page_Template */
    /* @var $wizard Application_Admin_Wizard */
    /* @var $steps Application_Admin_Wizard_Step[] */
    /* @var $activeStep Application_Admin_Wizard_Step */

    $wizard = $this->getVar('wizard');
    $steps = $this->getVar('steps');
    $activeStep = $this->getVar('activeStep');
    
    $this->ui->addStylesheet('ui-wizard.css');
    
    $nav = $this->page->createStepsNavigator();
    $nav->makeNumbered();
    
    foreach($steps as $step) 
    {
        $stepID = $step->getID();
        
        $url = '';
        
        if($step->isComplete()) 
        {
            $url = $step->getURLReview();
        }
        else 
        {
            $url = $step->getURL();
        }
            
        
        $nav->addStep($stepID, $step->getLabel())
        ->link($url)
        ->setEnabled($wizard->isValidStep($stepID));
    }
    
    $nav->selectStep($activeStep->getID());
    
    echo $nav->render();
    echo $activeStep->render();  
    