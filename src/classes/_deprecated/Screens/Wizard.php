<?php

declare(strict_types=1);

use Application\Admin\Wizard\BaseWizardMode;

/**
 * @package Application
 * @subpackage Admin
 * @deprecated Use {@see BaseWizardMode} instead.
 */
abstract class Application_Admin_Wizard extends Application_Admin_Area_Mode implements Application_Interfaces_Admin_Wizardable
{
    use Application_Traits_Admin_Wizard;

    protected function _handleBeforeActions() : void
    {
         $this->initWizard();
    }

    public function getDefaultSubmode() : string
    {
        return '';
    }

    public function getDefaultSubscreenClass() : null
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
