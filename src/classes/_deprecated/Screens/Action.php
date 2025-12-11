<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

use Application\Admin\Area\Mode\Submode\BaseAction;
use Application\Interfaces\Admin\AdminActionInterface;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;

/**
 * @package Application
 * @subpackage Administration
 * @deprecated Use {@see BaseAction} instead.
 */
abstract class Application_Admin_Area_Mode_Submode_Action extends Application_Admin_Skeleton implements AdminActionInterface
{
    use Application_Traits_Admin_Screen;
    
    protected AdminModeInterface $mode;
    protected AdminSubmodeInterface $submode;
    protected AdminAreaInterface $area;

    public function __construct(Application_Driver $driver, AdminSubmodeInterface $submode)
    {
        $this->adminMode = $submode->isAdminMode();
        $this->submode = $submode;
        $this->mode = $submode->getMode();
        $this->area = $submode->getArea();
        
        parent::__construct($driver, $submode);

        $this->initScreen();
    }
    
    public function getSubmode() : AdminSubmodeInterface
    {
        return $this->submode;
    }

    public function getMode() : AdminModeInterface
    {
        return $this->mode;
    }

    public function isUserAllowed() : bool
    {
        return true;
    }

    public function render() : string
    {
        return $this->renderContent();
    }
}
