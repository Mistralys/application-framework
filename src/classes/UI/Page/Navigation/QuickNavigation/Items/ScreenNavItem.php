<?php
/**
 * @package UI
 * @subpackage QuickNavigation
 * @see \UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem
 */

declare(strict_types=1);

namespace UI\Page\Navigation\QuickNavigation\Items;

use Application_Admin_ScreenInterface;
use Application_Request;
use UI\Page\Navigation\QuickNavigation;
use UI\Page\Navigation\QuickNavigation\BaseQuickNavItem;
use UI_Exception;
use UI_Page_Navigation;
use UI_Renderable_Interface;

/**
 * Navigation item for adding a link to an admin screen
 * in the application.
 *
 * @package UI
 * @subpackage QuickNavigation
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenNavItem extends BaseQuickNavItem
{
    /**
     * @var array<string,string>
     */
    private array $params = array();

    private string $label;
    private bool $newTab = false;

    /**
     * @param QuickNavigation $navigation
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param array<string,string> $params
     * @throws UI_Exception
     */
    public function __construct(QuickNavigation $navigation, $label, array $params=array())
    {
        parent::__construct($navigation);

        $this->label = toString($label);

        $this->setParams($params);
    }

    public function setAreaID(string $areaID) : self
    {
        $this->setParam(Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE, $areaID);
        return $this;
    }

    public function setModeID(string $modeID) : self
    {
        $this->setParam(Application_Admin_ScreenInterface::REQUEST_PARAM_MODE, $modeID);
        return $this;
    }

    public function setSubmodeID(string $submodeID) : self
    {
        $this->setParam(Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE, $submodeID);
        return $this;
    }

    public function setActionID(string $actionID) : self
    {
        $this->setParam(Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION, $actionID);
        return $this;
    }

    /**
     * @param array<string,string> $params
     * @return $this
     */
    public function setParams(array $params) : self
    {
        foreach($params as $name => $value)
        {
            $this->setParam($name, $value);
        }

        return $this;
    }

    public function setParam(string $name, string $value) : self
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function injectNavigation(UI_Page_Navigation $navigation) : void
    {
        $url = $navigation->addURL(
            $this->label,
            Application_Request::getInstance()->buildURL($this->params)
        )
            ->setIcon($this->getIcon());

        if(isset($this->tooltipInfo)) {
            $url->setTooltip($this->tooltipInfo->makeBottom());
        }

        if($this->newTab) {
            $url->makeNewTab();
        }
    }

    public function makeNewTab(bool $newTab=true) : self
    {
        $this->newTab = $newTab;
        return $this;
    }
}
