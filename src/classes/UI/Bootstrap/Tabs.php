<?php
/**
 * File containing the {@link UI_Bootstrap_Tabs} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_Tabs
 */

use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * Bootstrap tabs container.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_Tabs extends UI_Bootstrap
{
    public const ERROR_TAB_ALREADY_EXISTS = 18501;
    public const ERROR_TAB_NAME_NOT_FOUND = 18502;
    
    protected function init() : void
    {
        $this->addClass('nav');
        $this->addClass('nav-tabs');
    }
    
    public function getID() : string
    {
        return 'tabs-'.$this->getName();
    }
    
    protected function _render() : string
    {
        if(!$this->hasChildren()) {
            return '';
        }
        
        $this->setAttribute('id', $this->getID());
        
        ob_start();
        
        ?>
            <!-- start tabs -->
            <ul <?php echo $this->renderAttributes() ?>>
				<?php echo $this->renderTabs() ?>            	
        	</ul>
        	<!-- end tabs -->
    	<?php 
    	
    	$html = ob_get_clean();
            
        if($this->hasToggles())
        {
            $selected = $this->getSelectedTab();
            
            $this->ui->addJavascriptOnload(sprintf(
                "$('#%s').tab('show');",
                $selected->getLinkID()
            ));
            
            $html .= $this->renderBodies();
        }
            
        return $html;
    }
    
    protected function renderTabs() : string
    {
        $tabs = $this->getTabs();
        
        $html = '';
        
        foreach($tabs as $tab)
        {
            if($tab->isValid())
            {
                $html .= $tab->renderTab();
            }
        }
        
        return $html;
    }
    
    protected function renderBodies() : string
    {
        $tabs = $this->getTabs();
        
        ob_start();
        
        ?>
        <!-- start tabs contents -->
        <div class="tab-content">
        	<?php 
                foreach($tabs as $tab)
                {
                    if(!$tab->hasBody()) {
                        continue;
                    }
                    
                    $classes = array('tab-pane');
                    if($tab->isSelected()) {
                        $classes[] = 'active';
                    }
                    
                    ?>
                    	<!-- start tab <?php  echo $tab->getName() ?> -->
                        <div class="<?php echo implode(' ', $classes) ?>" id="<?php echo $tab->getID() ?>">
                            <?php $tab->display() ?>
                        </div>
                        <!-- end tab <?php  echo $tab->getName() ?> -->
                    <?php 
                }
            ?>
        </div>
        <?php 
            
        return ob_get_clean();
    }
    
    protected function hasToggles() : bool
    {
        $tabs = $this->getTabs();
        
        foreach($tabs as $tab) 
        {
            if($tab->hasBody()) {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * @return UI_Bootstrap_Tab
    */
    public function getSelectedTab() : UI_Bootstrap_Tab
    {
        $active = null;
        
        $tabs = $this->getTabs();
        foreach($tabs as $tab) {
            if($tab->isSelected()) {
                $active = $tab;
                break;
            }
        }
        
        if(!$active) {
            $active = array_shift($tabs);
            $this->selectTab($active);
        }
        
        return $active;
    }

    /**
     * Selects the active tab by fetching the tab name from
     * a request variable.
     *
     * @param string $varName
     * @return $this
     * @throws Application_Exception
     */
    public function selectByRequestVar(string $varName) : UI_Bootstrap_Tabs
    {
        $value = Application_Driver::getInstance()->getRequest()->getParam($varName);

        if(!empty($value) && $this->hasTab($value))
        {
            $this->selectTab($this->getTabByName($value));
        }

        return $this;
    }

    /**
     * Selects the active tab by fetching the name from the current `submode` request variable.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function selectBySubmode() : UI_Bootstrap_Tabs
    {
        return $this->selectByRequestVar(AdminScreenInterface::REQUEST_PARAM_SUBMODE);
    }

    /**
     * Selects the active tab by fetching the name from the current `action` request variable.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function selectByAction() : UI_Bootstrap_Tabs
    {
        return $this->selectByRequestVar(AdminScreenInterface::REQUEST_PARAM_ACTION);
    }

    /**
     * Selects the active tab by fetching the name from the current `mode` request variable.
     *
     * @return $this
     * @throws Application_Exception
     */
    public function selectByMode() : UI_Bootstrap_Tabs
    {
        return $this->selectByRequestVar(AdminScreenInterface::REQUEST_PARAM_MODE);
    }

    public function selectTab(UI_Bootstrap_Tab $target) : UI_Bootstrap_Tabs
    {
        $tabs = $this->getTabs();
        
        foreach($tabs as $tab) 
        {            
            $tab->deselect();
        }
        
        $target->select();
        
        return $this;
    }

    /**
     * Adds a new tab at the end of the tabs list.
     *
     * @param string $label
     * @param string $name
     * @return UI_Bootstrap_Tab
     * @throws Application_Exception
     */
    public function appendTab(string $label, string $name='') : UI_Bootstrap_Tab
    {
        $tab = new UI_Bootstrap_Tab($this->ui);
        $tab->setLabel($label);
        
        if(!empty($name)) {
            $tab->setName($name);
        }
        
        $this->appendChild($tab);

        return $tab;
    }
    
    public function hasTab(string $name) : bool
    {
        $tabs = $this->getTabs();
        
        foreach($tabs as $tab) {
            if($tab->getName() === $name) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getTabByName(string $name) : UI_Bootstrap_Tab
    {
        $tabs = $this->getTabs();
        
        foreach($tabs as $tab) {
            if($tab->getName() === $name) {
                return $tab;
            }
        }
        
        throw new Application_Exception(
            'No such tab found.',
            sprintf(
                'The tab [%s] was not found in the tabs.',
                $name
            ),
            self::ERROR_TAB_NAME_NOT_FOUND
        );
    }
    
   /**
    * @return UI_Bootstrap_Tab[]
    */
    public function getTabs() : array
    {
        $children = $this->getChildren();
        $result = array();

        foreach($children as $child)
        {
            if($child instanceof UI_Bootstrap_Tab)
            {
                $result[] = $child;
            }
        }

        return $result;
    }
}
