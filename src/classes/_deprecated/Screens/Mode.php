<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

use Application\Admin\Area\BaseMode;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;

/**
 * @package Application
 * @subpackage Administration
 * @deprecated Use {@see BaseMode} instead.
 */
abstract class Application_Admin_Area_Mode extends Application_Admin_Skeleton implements AdminModeInterface
{
    use Application_Traits_Admin_Screen;
    
    protected AdminAreaInterface $area;

    public function __construct(Application_Driver $driver, AdminAreaInterface $area)
    {
        $this->adminMode = $area->isAdminMode();
        $this->area = $area;
        
        parent::__construct($driver, $area);

        $this->initScreen();
    }
    
    public function hasSubmodes() : bool
    {
        return $this->hasSubscreens();
    }
    
    public function getDefaultSubscreenID() : string
    {
        return $this->getDefaultSubmode();
    }

    public function getSubmode() : ?AdminSubmodeInterface
    {
        $screen = $this->getActiveSubscreen();
        
        if($screen instanceof AdminSubmodeInterface)
        {
            return $screen;
        }

        return null;
    }

    public function render() : string
    {
        return $this->renderContent();
    }
}
