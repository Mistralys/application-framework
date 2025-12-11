<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

use Application\Admin\Area\Mode\BaseSubmode;
use Application\Interfaces\Admin\AdminActionInterface;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;
use AppUtils\ClassHelper;

/**
 * Base class for sub-mode admin screens.
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @deprecated Use {@see BaseSubmode} instead.
 */
abstract class Application_Admin_Area_Mode_Submode extends Application_Admin_Skeleton implements AdminSubmodeInterface
{
    use Application_Traits_Admin_Screen;

    protected AdminModeInterface $mode;
    protected AdminAreaInterface $area;

    public function __construct(Application_Driver $driver, AdminModeInterface $mode)
    {
        $this->adminMode = $mode->isAdminMode();
        $this->mode = $mode;
        $this->area = $mode->getArea();

        parent::__construct($driver, $mode);

        $this->initScreen();
    }

    final public function getMode() : AdminModeInterface
    {
        return $this->mode;
    }

    /**
     * Checks whether this submode has separate action classes.
     * 
     * @return boolean
     */
    final public function hasActions() : bool
    {
        return $this->hasSubscreens();
    }

    public function getDefaultSubscreenID() : string
    {
        return $this->getDefaultAction();
    }
    
    /**
     * Retrieves the ID of the currently selected action.
     * 
     * @return string
     */
    final public function getActionID() : string
    {
        return $this->getActiveSubscreenID();
    }

    /**
     * Retrieves the currently active action, or null if no actions are available.
     * @return AdminActionInterface|NULL
     */
    final public function getAction() : ?AdminActionInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            AdminActionInterface::class,
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
