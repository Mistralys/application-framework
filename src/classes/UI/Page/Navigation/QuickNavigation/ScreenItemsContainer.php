<?php
/**
 * @package UI
 * @subpackage QuickNavigation
 * @see \UI\Page\Navigation\QuickNavigation\ScreenItemsContainer
 */

declare(strict_types=1);

namespace UI\Page\Navigation\QuickNavigation;

use UI\AdminURLs\AdminURLInterface;
use UI\Page\Navigation\QuickNavigation;
use UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem;
use UI\Page\Navigation\QuickNavigation\Items\URLNavItem;
use UI_Exception;
use UI_Page_Navigation;
use UI_Renderable_Interface;

/**
 * Container for navigation items tied to a specific
 * administration screen, to keep them separate from
 * each other.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenItemsContainer
{
    private QuickNavigation $quickNav;
    private bool $exclusive = false;

    /**
     * @var BaseQuickNavItem[]
     */
    private array $items = array();

    public function __construct(QuickNavigation $navigation)
    {
        $this->quickNav = $navigation;
    }

    public function hasItems() : bool
    {
        return count($this->getValidItems()) !== 0;
    }

    public function getValidItems() : array
    {
        $valid = array();

        foreach($this->items as $item)
        {
            if($item->isValid())
            {
                $valid[] = $item;
            }
        }

        return $valid;
    }

    public function makeExclusive() : self
    {
        $this->exclusive = true;
        return $this;
    }

    public function isExclusive() : bool
    {
        return $this->exclusive;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|AdminURLInterface $url
     * @return URLNavItem
     *
     * @throws UI_Exception
     */
    public function addURL($label, $url) : URLNavItem
    {
        $item = new URLNavItem($this->quickNav, $label, $url);

        $this->items[] = $item;

        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param array $params
     * @return ScreenNavItem
     * @throws UI_Exception
     */
    public function addScreen($label, array $params=array()) : ScreenNavItem
    {
        $item = new ScreenNavItem($this->quickNav, $label, $params);

        $this->items[] = $item;

        return $item;
    }

    public function injectElements(UI_Page_Navigation $navigation)  : void
    {
        $valid = $this->getValidItems();

        foreach($valid as $item)
        {
            $item->injectNavigation($navigation);
        }
    }
}
