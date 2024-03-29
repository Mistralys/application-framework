<?php
/**
 * File containing the {@see Application_Admin_Area_Mode_Submode} class.
 * 
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Area_Mode_Submode
 */

use AppUtils\ClassHelper;

/**
 * Base class for sub-mode admin screens.
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Mode_Submode extends Application_Admin_Skeleton
{
    use Application_Traits_Admin_Screen;

    protected Application_Admin_Area_Mode $mode;
    protected Application_Admin_Area $area;

    public function __construct(Application_Driver $driver, Application_Admin_Area_Mode $mode)
    {
        $this->adminMode = $mode->isAdminMode();
        $this->mode = $mode;
        $this->area = $mode->getArea();

        parent::__construct($driver, $mode);

        $this->initScreen();
    }

   /**
    * @return Application_Admin_Area_Mode
    */
    public function getMode() : Application_Admin_Area_Mode
    {
        return $this->mode;
    }

    /**
     * Checks whether this submode has separate action classes.
     * 
     * @return boolean
     */
    public function hasActions() : bool
    {
        return $this->hasSubscreens();
    }

    abstract public function getDefaultAction() : string;

    public function getDefaultSubscreenID() : string
    {
        return $this->getDefaultAction();
    }
    
    /**
     * Retrieves the ID of the currently selected action.
     * 
     * @return string
     */
    protected function getActionID() : string
    {
        return $this->getActiveSubscreenID();
    }

    /**
     * Retrieves the currently active action, or null if no actions are available.
     * @return Application_Admin_Area_Mode_Submode_Action|NULL
     */
    public function getAction() : ?Application_Admin_Area_Mode_Submode_Action
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Admin_Area_Mode_Submode_Action::class,
            $this->getActiveSubscreen()
        );
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
