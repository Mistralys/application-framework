<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

use Application\Admin\Area\BaseMode;

/**
 * @package Application
 * @subpackage Administration
 * @deprecated Use {@see BaseMode} instead.
 */
abstract class Application_Admin_Area_Mode extends Application_Admin_Skeleton
{
    use Application_Traits_Admin_Screen;
    
    protected Application_Admin_Area $area;

    public function __construct(Application_Driver $driver, Application_Admin_Area $area)
    {
        $this->adminMode = $area->isAdminMode();
        $this->area = $area;
        
        parent::__construct($driver, $area);

        $this->initScreen();
    }
    
    abstract public function getDefaultSubmode() : string;

    public function hasSubmodes() : bool
    {
        return $this->hasSubscreens();
    }
    
    public function getDefaultSubscreenID() : string
    {
        return $this->getDefaultSubmode();
    }

    public function getSubmode() : ?Application_Admin_Area_Mode_Submode
    {
        $screen = $this->getActiveSubscreen();
        
        if($screen instanceof Application_Admin_Area_Mode_Submode)
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
