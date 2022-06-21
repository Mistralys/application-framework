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
use UI\Page\Navigation\QuickNavigation;
use UI_Page_Navigation;

/**
 * Abstract base class for navigation items in the quick navigation.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseQuickNavItem implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;

    private QuickNavigation $quickNav;

    public function __construct(QuickNavigation $navigation)
    {
        $this->quickNav = $navigation;
    }

    public function next() : QuickNavigation
    {
        return $this->quickNav;
    }

    abstract public function injectNavigation(UI_Page_Navigation $navigation) : void;
}
