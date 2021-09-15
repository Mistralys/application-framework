<?php

abstract class Application_Admin_Wizard extends Application_Admin_Area_Mode implements Application_Interfaces_Admin_Wizardable
{
    use Application_Traits_Admin_Wizard;

    protected function _handleBeforeActions()
    {
         $this->initWizard();
    }

    public function getDefaultSubmode()
    {
        return null;
    }
    
    public function getLogIdentifier() : string
    {
        if(empty($this->sessionID))
        {
            return sprintf(
                'Wizard [%s] | Session [%s]',
                $this->getID(),
                $this->sessionID
            );
        }

        return sprintf(
            'Wizard [%s]',
            $this->getID()
        );
    }
}
