<?php
/**
 * @package UI
 * @subpackage QuickNavigation
 * @see \UI\Page\Navigation\QuickNavigation\Items\URLNavItem
 */

declare(strict_types=1);

namespace UI\Page\Navigation\QuickNavigation\Items;

use UI\AdminURLs\AdminURL;
use UI\Page\Navigation\QuickNavigation;
use UI\Page\Navigation\QuickNavigation\BaseQuickNavItem;
use UI_Exception;
use UI_Page_Navigation;
use UI_Renderable_Interface;

/**
 * Navigation item for adding a simple URL, with the
 * option to make it open in a new tab.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLNavItem extends BaseQuickNavItem
{
    private string $label;
    private string $url;
    private bool $external = false;

    /**
     * @param QuickNavigation $quickNavigation
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|AdminURL $url
     * @throws UI_Exception
     */
    public function __construct(QuickNavigation $quickNavigation, $label, $url)
    {
        parent::__construct($quickNavigation);

        $this->label = toString($label);
        $this->url = (string)$url;
    }

    public function injectNavigation(UI_Page_Navigation $navigation) : void
    {
        $url = $navigation->addURL($this->label, $this->url)
            ->setIcon($this->getIcon());

        if(isset($this->tooltipInfo)) {
            $url->setTooltip($this->tooltipInfo->makeBottom());
        }

        if($this->external) {
            $url->makeNewTab();
        }
    }
    
    public function makeNewTab(bool $external=true) : self
    {
        $this->external = $external;
        return $this;
    }
}
