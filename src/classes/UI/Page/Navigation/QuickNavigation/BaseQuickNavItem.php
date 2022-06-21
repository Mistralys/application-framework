<?php
/**
 * @package UI
 * @subpackage QuickNavigation
 * @see \UI\Page\Navigation\QuickNavigation\BaseQuickNavItem
 */

declare(strict_types=1);

namespace UI\Page\Navigation\QuickNavigation;

use Application_Interfaces_Iconizable;
use Application_Traits_Iconizable;
use AppUtils\Traits\RenderableTrait;
use UI\Interfaces\TooltipableInterface;
use UI\Page\Navigation\QuickNavigation;
use UI\Traits\TooltipableTrait;
use UI_Exception;
use UI_Interfaces_Conditional;
use UI_Page_Navigation;
use UI_Renderable_Interface;
use UI_Traits_Conditional;

/**
 * Abstract base class for navigation items in the quick navigation.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseQuickNavItem
    implements
    Application_Interfaces_Iconizable,
    UI_Interfaces_Conditional,
    TooltipableInterface
{
    use Application_Traits_Iconizable;
    use UI_Traits_Conditional;
    use TooltipableTrait;
    use RenderableTrait;

    private QuickNavigation $quickNav;

    public function __construct(QuickNavigation $navigation)
    {
        $this->quickNav = $navigation;
    }

    public function next() : QuickNavigation
    {
        return $this->quickNav;
    }

    public function render() : string
    {
        return '';
    }

    abstract public function injectNavigation(UI_Page_Navigation $navigation) : void;
}
