<?php
/**
 * File containing the {@link UI_Page_Sidebar} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Sidebar
 */

use Application\Revisionable\RevisionableInterface;
use AppUtils\Interfaces\StringableInterface;

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
class UI_Page_Sidebar implements
    Application_LockableItem_Interface,
    UI_Renderable_Interface
{
    use Application_Traits_LockableItem;
    use Application_Traits_LockableStatus;
    use UI_Traits_RenderableGeneric;

    public const DEFAULT_ELEMENT_ID = 'sidebar';
    private UI_Page $page;
    private ?UI_Page_Template $template = null;
    protected string $instanceID;
    private bool $collapsed = false;
    private static int $instanceCounter = 0;
    private string $id;

    /**
     * @var UI_Page_Sidebar_ItemInterface[]
     */
    private array $items = array();

    /**
     * @var string[]
     */
    protected array $classes = array();

    public function __construct(?string $id=null, ?UI_Page $page=null)
    {
        self::$instanceCounter++;

        if(empty($id)) {
            $id = self::DEFAULT_ELEMENT_ID;
        }

        if($page === null) {
            $page = UI::getInstance()->getPage();
        }

        $this->id = $id;
        $this->instanceID = 'sidebar'.self::$instanceCounter;
        $this->page = $page;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function setID(string $id) : self
    {
        $this->id = $id;
        return $this;
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
    public function makeCollapsed() : self
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
    public function addButton(string $name, $title = ''): UI_Page_Sidebar_Item_Button
    {
        $item = $this->createButton($name, $title);
        $this->items[] = $item;

        return $item;
    }
    
    /**
     * @param string $name
     * @param string|StringableInterface|NULL $title
     * @return UI_Page_Sidebar_Item_DropdownButton
     */
    public function addDropdownButton(string $name, $title=null): UI_Page_Sidebar_Item_DropdownButton
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
            if($item instanceof UI_Page_Sidebar_Item_Button && $item->getName() === $name)
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
        foreach ($this->items as $item) {
            if($item instanceof UI_Page_Sidebar_Item_Button && $item->getName() === $name) {
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
    * @param RevisionableInterface $revisionable
    * @return UI_Page_Sidebar_Item_Message
    */
    public function addRevisionableStateInfo(RevisionableInterface $revisionable) : UI_Page_Sidebar_Item_Message
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
        
        $message->requireFalse($revisionable->requireState()->isChangingAllowed());
        
        return $message;
    }
    
   /**
    * Creates a sidebar section
    * @return UI_Page_Section
    */
    public function addSection(): UI_Page_Section
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
    public function addFormTOC(UI_Form $form) : UI_Page_Sidebar_Item_FormTOC
    {
        $item = new UI_Page_Sidebar_Item_FormTOC($this, $form);
        $this->items[] = $item;
        
        return $item;
    }

    public function addFormableTOC(Application_Interfaces_Formable $formable) : UI_Page_Sidebar_Item_FormTOC
    {
        return $this->addFormTOC($formable->getFormInstance());
    }

    /**
     * Adds the content of any template to the sidebar.
     * 
     * @param string $templateID
     * @param array<string,mixed> $params
     * @return UI_Page_Sidebar_Item_Template
     */
    public function addTemplate(string $templateID, array $params = array()): UI_Page_Sidebar_Item_Template
    {
        $item = new UI_Page_Sidebar_Item_Template($this, $templateID, $params);
        $this->items[] = $item;

        return $item;
    }

    /**
     * Adds a collapsible help block with a short informational
     * text for the current page.
     *
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @param string|number|UI_Renderable_Interface|NULL $content
     * @param bool $startCollapsed
     * @return UI_Page_Sidebar_Item_Template
     * @throws UI_Exception
     */
    public function addHelp($title, $content, bool $startCollapsed=true) : UI_Page_Sidebar_Item_Template
    {
        $tmpl = new UI_Page_Sidebar_Item_Template($this, 'sidebar.helpblock');
        $tmpl->setVar('title', toString($title));
        $tmpl->setVar('content', toString($content));
        $tmpl->setVar('collapsed', $startCollapsed);
        $this->items[] = $tmpl;
        
        return $tmpl;
    }

    /**
     * @param string|StringableInterface|NULL $content
     * @throws InvalidArgumentException
     * @return UI_Page_Sidebar_Item_Custom
     */
    public function addCustom($content) : UI_Page_Sidebar_Item_Custom
    {
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
    public function addInfoMessage($message, bool $icon=false, bool $dismissable=false) : UI_Page_Sidebar_Item_Message
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
    public function addErrorMessage($message, bool $icon=false, bool $dismissable=false) : UI_Page_Sidebar_Item_Message
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
    public function addSuccessMessage($message, bool $icon=false, bool $dismissable=false): UI_Page_Sidebar_Item_Message
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
    public function addWarningMessage($message, bool $icon=false, bool $dismissable=false): UI_Page_Sidebar_Item_Message
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
    public function addMessage($message, string $type = UI::MESSAGE_TYPE_INFO, bool $icon=false, bool $dismissable=false): UI_Page_Sidebar_Item_Message
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

            if($previous && $item->isSeparator() && $previous->isSeparator()) {
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

    public function getUI() : UI
    {
        return $this->page->getUI();
    }

    /**
     * @return $this
     */
    public function makeLarger() : self
    {
        return $this->addClass('large');
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addClass(string $name) : self
    {
        if (!in_array($name, $this->classes, true)) {
            $this->classes[] = $name;
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getClasses() : array
    {
        return $this->classes;
    }

    public function isLarge() : bool
    {
        return in_array('large', $this->classes, true);
    }
    
   /**
    * Adds a sidebar section available only to developers,
    * and styled as such.
    * 
    * @return UI_Page_Sidebar_Item_DeveloperPanel
    */
    public function addDeveloperPanel(): UI_Page_Sidebar_Item_DeveloperPanel
    {
        $panel = new UI_Page_Sidebar_Item_DeveloperPanel($this);
        $this->items[] = $panel;
        
        return $panel;
    }
    
    public function addFilterSettings(Application_FilterSettings $settings, $title=null): UI_Page_Sidebar_Item_Template
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
