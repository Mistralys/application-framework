<?php
/**
 * File containing the {@link UI_Page_Sidebar} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Sidebar
 */

/**
 * Handles the sidebar in the application's UI. Provides an API to easily
 * add items in the sidebar.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_frame_sidebar
 */
class UI_Page_Sidebar implements Application_LockableItem_Interface
{
    use Application_Traits_LockableItem;
    use Application_Traits_LockableStatus;

    /**
     * @var UI_Page
     */
    private $page;

   /**
    * @var UI_Page_Sidebar_ItemInterface[]
    */
    private $items = array();

    /**
     * @var UI_Page_Template
     */
    private $template;

    /**
     * @var string[]
     */
    protected $classes = array();

    /**
     * @var string
     */
    protected $instanceID;

    /**
     * @var bool
     */
    private $collapsed = false;

    private static $instanceCounter = 0;

    public function __construct(UI_Page $page)
    {
        self::$instanceCounter++;

        $this->instanceID = 'sidebar'.self::$instanceCounter;
        $this->page = $page;
    }
    
    public function getInstanceID() : string
    {
        return $this->instanceID;
    }

    public function isCollapsed() : bool
    {
        return $this->collapsed;
    }

    /**
     * Collapses the sidebar.
     * @return $this
     */
    public function makeCollapsed()
    {
        $this->collapsed = true;
        return $this;
    }

    public function render() : string
    {
        $items = $this->getItems();
        
        if($this->locked) {
            foreach($items as $item) {
                if($item instanceof Application_LockableItem_Interface) {
                    $item->lock($this->lockReason);
                }
            }
        }
        
        $template = $this->getTemplate();
        
        $template->setVar('sidebar', $this);
        $template->setVar('classes', $this->classes);

        return $template->render();
    }

    public function display() : void
    {
        echo $this->render();
    }

    public function hasItems() : bool
    {
        $items = $this->getItems();
        return !empty($items);
    }

    /**
     * @return UI_Page_Template
     * @throws Application_Exception
     */
    public function getTemplate() : UI_Page_Template
    {
        if(!isset($this->template)) {
            $this->template = $this->page->createTemplate('frame.sidebar');
        }
        
        return $this->template;
    }

    /**
     * @return UI_Page
     */
    public function getPage() : UI_Page
    {
        return $this->page;
    }

    /**
     * @param string $name
     * @param string|UI_Renderable_Interface|int|float $title
     * @return UI_Page_Sidebar_Item_Button
     */
    public function addButton(string $name, $title = '')
    {
        $item = $this->createButton($name, $title);
        $this->items[] = $item;

        return $item;
    }
    
    /**
     * @param string $name
     * @param string $title
     * @return UI_Page_Sidebar_Item_DropdownButton
     */
    public function addDropdownButton($name, $title=null)
    {
        $item = $this->createDropdownButton($name, $title);
        $this->items[] = $item;
        
        return $item;
    }
    
   /**
    * Attempts to retrieve a button by its name.
    * 
    * @param string $name
    * @return UI_Page_Sidebar_Item_Button|NULL
    */
    public function getButton(string $name) : ?UI_Page_Sidebar_Item_Button
    {
        foreach ($this->items as $item)
        {
            if($item instanceof UI_Page_Sidebar_Item_Button)
            {
                return $item;
            }
        }

        return null;
    }
    
   /**
    * Checks if a button with this name exists.
    * @param string $name
    * @return bool
    */
    public function hasButton(string $name) : bool
    {
        $total = count($this->items);
        for($i=0; $i < $total; $i++) {
            if($this->items[$i] instanceof UI_Page_Sidebar_Item_Button && $this->items[$i]->getName() == $name) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param string $name
     * @param string|UI_Renderable_Interface|int|float $title
     * @return UI_Page_Sidebar_Item_Button
     */
    public function createButton(string $name, $title = '') : UI_Page_Sidebar_Item_Button
    {
        return new UI_Page_Sidebar_Item_Button($this, $name, $title);
    }

    /**
     * @param string $name
     * @param string|UI_Renderable_Interface|int|float $title
     * @return UI_Page_Sidebar_Item_DropdownButton
     */
    public function createDropdownButton(string $name, $title = '') : UI_Page_Sidebar_Item_DropdownButton
    {
        return new UI_Page_Sidebar_Item_DropdownButton($this, $name, $title);
    }

    /**
     * @return UI_Page_Sidebar_Item_Separator|NULL
     */
    public function addSeparator() : ?UI_Page_Sidebar_Item_Separator
    {
        if (!$this->hasItems()) {
            return null;
        }

        $item = new UI_Page_Sidebar_Item_Separator($this);
        $this->items[] = $item;

        return $item;
    }
    
   /**
    * Adds a message informing the user that no changes may be
    * made to the revisionable if it is in a state that does
    * not allow modifying it.
    * 
    * @param Application_RevisionableCollection_DBRevisionable $revisionable
    * @return UI_Page_Sidebar_Item_Message
    */
    public function addRevisionableStateInfo(Application_RevisionableCollection_DBRevisionable $revisionable) : UI_Page_Sidebar_Item_Message
    {
        $message = $this->addInfoMessage(
            UI::icon()->information().' '.
            '<b>'.
                t(
                    'The %1$s is in the %2$s state:', 
                    $revisionable->getCollection()->getRecordReadableNameSingular(),
                    $revisionable->getCurrentPrettyStateLabel()
                ).
            '</b> '.
            t('No changes may be made.')
        );
        
        $message->requireFalse($revisionable->getState()->isChangingAllowed());
        
        return $message;
    }
    
   /**
    * Creates a sidebar section
    * @return UI_Page_Section
    */
    public function addSection()
    {
        $section = $this->page->createSidebarSection();
        
        $this->items[] = $section;
        
        return $section;
    }

   /**
    * Adds a table of contents for the specified form.
    * @param UI_Form $form
    * @return UI_Page_Sidebar_Item_FormTOC
    */
    public function addFormTOC(UI_Form $form)
    {
        $item = new UI_Page_Sidebar_Item_FormTOC($this, $form);
        $this->items[] = $item;
        
        return $item;
    }

    /**
     * Adds the content of any template to the sidebar.
     * 
     * @param string $templateID
     * @param array $params
     * @return UI_Page_Sidebar_Item_Template
     */
    public function addTemplate($templateID, $params = array())
    {
        $item = new UI_Page_Sidebar_Item_Template($this, $templateID, $params);
        $this->items[] = $item;

        return $item;
    }
    
   /**
    * Adds a collapsible help block with a short informational
    * text for the current page.
    * 
    * @param string $title
    * @param string $content
    * @param bool $startCollapsed
    * @return UI_Page_Sidebar_Item_Template
    */
    public function addHelp($title, $content, $startCollapsed=true)
    {
        $tmpl = new UI_Page_Sidebar_Item_Template($this, 'sidebar.helpblock');
        $tmpl->setVar('title', $title);
        $tmpl->setVar('content', $content);	
        $tmpl->setVar('collapsed', $startCollapsed);
        $this->items[] = $tmpl;
        
        return $tmpl;
    }

    /**
     * @param string $content
     * @throws InvalidArgumentException
     * @return UI_Page_Sidebar_Item_Custom
     */
    public function addCustom($content)
    {
        if (!is_string($content)) {
            throw new InvalidArgumentException('You may only use strings as custom sidebar content, ' . gettype($content) . 'given.');
        }

        $item = new UI_Page_Sidebar_Item_Custom($this, $content);
        $this->items[] = $item;

        return $item;
    }

   /**
    * Adds an information-styled message.
    * @param string|number|UI_Renderable_Interface $message
    * @param bool $icon
    * @param bool $dismissable
    * @return UI_Page_Sidebar_Item_Message
    */
    public function addInfoMessage($message, bool $icon=false, bool $dismissable=false)
    {
        return $this->addMessage($message, UI::MESSAGE_TYPE_INFO, $icon, $dismissable);
    }

   /**
    * Adds an error-styled message.
    * @param string|number|UI_Renderable_Interface $message
    * @param bool $icon
    * @param bool $dismissable
    * @return UI_Page_Sidebar_Item_Message
    */
    public function addErrorMessage($message, bool $icon=false, bool $dismissable=false)
    {
        return $this->addMessage($message, UI::MESSAGE_TYPE_ERROR, $icon, $dismissable);
    }

   /**
    * Adds a success-styled message.
    * @param string|number|UI_Renderable_Interface $message
    * @param bool $icon
    * @param bool $dismissable
    * @return UI_Page_Sidebar_Item_Message
    */
    public function addSuccessMessage($message, bool $icon=false, bool $dismissable=false)
    {
        return $this->addMessage($message, UI::MESSAGE_TYPE_SUCCESS, $icon, $dismissable);
    }

    /**
     * Adds a warning-styled message.
     * @param string|number|UI_Renderable_Interface $message
     * @param bool $icon
     * @param bool $dismissable
     * @return UI_Page_Sidebar_Item_Message
     */
    public function addWarningMessage($message, bool $icon=false, bool $dismissable=false)
    {
        return $this->addMessage($message, UI::MESSAGE_TYPE_WARNING, $icon, $dismissable);
    }
    
   /**
    * Creates a message item instance and returns it.
    * @param string|number|UI_Renderable_Interface $message
    * @param string $type
    * @param bool $icon
    * @param bool $dismissable
    * @return UI_Page_Sidebar_Item_Message
    */
    public function addMessage($message, $type = UI::MESSAGE_TYPE_INFO, bool $icon=false, bool $dismissable=false)
    {
        $item = new UI_Page_Sidebar_Item_Message($this, $message, $type, $icon, $dismissable);
        
        $this->items[] = $item;

        return $item;
    }

    /**
     * @return UI_Page_Sidebar_Item[]
     */
    public function getItems() : array
    {
        $items = $this->normalizeSeparators();

        $this->registerSiblings($items);
        
        return $items;
    }

    private function registerSiblings(array $items) : void
    {
        $total = count($items);

        // now tell all items about their siblings
        for($i=0; $i < $total; $i++) {
            $item = $items[$i];
            $next = null;
            $prev = null;

            if(isset($items[($i+1)])) {
                $next = $items[($i+1)];
            }

            if(isset($items[($i-1)])) {
                $prev = $items[($i-1)];
            }

            $item->registerPosition($prev, $next);
        }
    }

    /**
     * @return UI_Page_Sidebar_Item[]
     */
    private function normalizeSeparators() : array
    {
        $items = $this->getValidItems();

        // ensure no double separators
        $total = count($items);
        $previous = null;
        $result = array();
        for($i=0; $i < $total; $i++) {
            $item = $items[$i];

            if($item->isSeparator() && $previous && $previous->isSeparator()) {
                continue;
            }

            $previous = $item;
            $result[] = $item;
        }

        // items start with a separator, strip that
        if(!empty($result) && $result[0]->isSeparator()) {
            array_shift($result);
        }

        // ensure that the list does not end with a separator
        if(!empty($result)) {
            $last = array_pop($result);
            if(!$last->isSeparator()) {
                $result[] = $last;
            }
        }

        return $result;
    }

    private function getValidItems() : array
    {
        $result = array();
        foreach($this->items as $item)
        {
            if($item instanceof UI_Interfaces_Conditional)
            {
                if($item->isValid()) {
                    $result[] = $item;
                }
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return UI
     */
    public function getUI()
    {
        return $this->page->getUI();
    }

    public function makeLarger()
    {
        $this->addClass('large');
    }

    public function addClass($name)
    {
        if (!in_array($name, $this->classes)) {
            $this->classes[] = $name;
        }
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function isLarge()
    {
        return in_array('large', $this->classes);
    }
    
   /**
    * Adds a sidebar section available only to developers,
    * and styled as such.
    * 
    * @return UI_Page_Sidebar_Item_DeveloperPanel
    */
    public function addDeveloperPanel()
    {
        $panel = new UI_Page_Sidebar_Item_DeveloperPanel($this);
        $this->items[] = $panel;
        
        return $panel;
    }
    
    public function addFilterSettings(Application_FilterSettings $settings, $title=null)
    {
        return $this->addTemplate(
            'sidebar.filter-settings',
            array(
                'settings' => $settings,
                'title' => $title
            )
        );
    }
    
    protected string $tagName = 'div';
    
    public function setTagName(string $name) : self
    {
        $this->tagName = $name;
        return $this;
    }
    
    public function getTagName() : string
    {
        return $this->tagName;
    }
    
    public function makeAllItemsLockable() : self
    {
        foreach($this->items as $item) {
            if($item instanceof Application_LockableItem_Interface) {
                $item->makeLockable();
            }
        }

        return $this;
    }

    /**
     * The sidebar is always lockable.
     * @return bool
     */
    public function isLockable() : bool
    {
        return true;
    }
}
