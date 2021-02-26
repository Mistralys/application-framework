<?php

abstract class Application_Admin_Wizard extends Application_Admin_Area_Mode implements Application_Interfaces_Admin_Wizardable
{
    use Application_Traits_Admin_Wizard;
    
    public function getDefaultSubmode()
    {
        return null;
    }
    
    public function log(string $message) : void
    {
        if(!empty($this->sessionID)) 
        {
            $message = sprintf(
                'Wizard [%s] | %s',
                $this->sessionID,
                $message
            );
        }
        else
        {
            $message = 'Wizard | '.$message;
        }
        
        parent::log($message);
    }
}
