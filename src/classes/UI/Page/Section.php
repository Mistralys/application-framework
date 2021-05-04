<?php
/**
 * File containing the {@link UI_Page_Section} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Section
 */

use AppUtils\Traits_Classable;
use AppUtils\Interface_Classable;

/**
 * Helper class for creating and rendering content sections
 * in the UI. Offers an easy API to configure a section and
 * render/display it in several ways.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page::createSection()
 * @see template_default_frame_content_section
 * @see template_default_frame_sidebar_section
 */
abstract class UI_Page_Section extends UI_Renderable implements UI_Renderable_Interface, UI_Interfaces_Conditional, Application_LockableItem_Interface, UI_Page_Sidebar_ItemInterface, Application_Interfaces_Iconizable, Interface_Classable
{
    use Traits_Classable;
    use UI_Traits_Conditional;
    
    const ERROR_INVALID_CONTEXT_BUTTON = 511001;
    const ERROR_TAB_ALREADY_EXISTS = 511002;
    
    protected $templateName = 'frame.content.section';

    /**
     * @var array<int,UI_Button|UI_Bootstrap_ButtonDropdown>
     */
    protected $contextButtons = array();
    
   /**
    * The section properties: these get submitted as is
    * to the template that renders the HTML.
    * 
    * @var array<string,mixed>
    * @see setProperty()
    * @see getProperty()
    */
    protected $properties = array(
        'id' => '',
        'title' => '',
        'tagline' => '',
        'abstract' => '',
        'content' => '',
        'icon' => null,
        'collapsible' => false,
        'collapsed' => false,
        'visible-if-empty' => false,
        'group' => '_default',
        'type' => 'content-section',
        'anchor' => ''
    );

    /**
     * @var UI_QuickSelector|NULL
     */
    private $quickSelector = null;

    /**
     * Every section gets a dynamically created ID. This can
     * be overridden by setting the ID explicitly after creation
     * using the {@link setID()} method.
     *
     * @see UI_Page::createSection()
     */
    protected function initRenderable() : void
    {
        $this->setProperty('id', nextJSID());
        
        $this->addClass('content-section');
    }

    public function getType() : string
    {
        return (string)$this->getProperty('type');
    }

   /**
    * Sets the name of the template that will be used to
    * render the section. Default is the <code>frame.content.section</code>
    * template.
    * 
    * @param string $templateName
    * @return $this
    */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        return $this;
    }
    
   /**
    * Overrides the section's automatic ID. Use this if you need
    * to make sure the section always has the same ID.
    * 
    * @param string $id
    * @return $this
    */
    public function setID($id)
    {
        return $this->setProperty('id', $id);
    }
    
   /**
    * Retrieves the section's ID attribute.
    * @return string
    */
    public function getID()
    {
        return $this->getProperty('id');
    }
    
   /**
    * Optional. Sets the section's heading title.
    * @param string|number|UI_Renderable_Interface $title
    * @return $this
    */
    public function setTitle($title)
    {
        return $this->setProperty('title', toString($title));
    }

    /**
     * @param string $anchor
     * @return $this
     */
    public function setAnchor(string $anchor)
    {
        return $this->setProperty('anchor', $anchor);
    }

    public function getAnchor() : string
    {
        return strval($this->getProperty('anchor'));
    }

   /**
    * Wheter to skip the section rendering if its contents are empty.
    * @return boolean
    */
    public function isVisibleIfEmpty()
    {
        if($this->getProperty('visible-if-empty') === true) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Turns off the behavior that a section with empty
    * content is not displayed in the generated HTML.
    * This is mainly used to allows using a sections 
    * clientside, and fill it there. 
    * 
    * @param bool $visible
    * @return $this
    */
    public function setVisibleIfEmpty($visible=true)
    {
        return $this->setProperty('visible-if-empty', $visible);
    }
    
    public function setGroup($group)
    {
        return $this->setProperty('group', $group);
    }
    
    public function getGroup()
    {
        return $this->getProperty('group');
    }
    
    const BACKGROUND_TYPE_LIGHT = 'light';
    
   /**
    * Makes the section's background a solid color, with the specified
    * style. Use this in cases where the section should not have a transparent
    * background.
    * 
    * @param string $type
    * @return $this
    */
    public function makeSolidBackground(string $type) : UI_Page_Section
    {
        $this->addClass('solid-background-'.$type);
        return $this;
    }
    
   /**
    * Gives the section a light background color, as defined
    * in the CSS class "solid-background-light".
    * 
    * @return $this
    */
    public function makeLightBackground() : UI_Page_Section
    {
        return $this->makeSolidBackground(self::BACKGROUND_TYPE_LIGHT);
    }
    
   /**
    * Retrieves the current title for the section's heading.
    * @return string
    */
    public function getTitle() : string
    {
        return (string)$this->getProperty('title');
    }
    
   /**
    * Optional. Sets the tagline under the title. 
    * Note: only used if a title is also set.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setTagline($text)
    {
        return $this->setProperty('tagline', toString($text));
    }

    public function getTagline() : string
    {
        return strval($this->getProperty('tagline'));
    }

    public function hasTagline() : bool
    {
        return $this->getTagline() !== '';
    }
    
   /**
    * Optional. Sets an abstract text that explains the contents of the section.
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function setAbstract($text)
    {
        return $this->setProperty('abstract', toString($text));
    }

    public function getAbstract() : string
    {
        return (string)$this->getProperty('abstract');
    }

   /**
    * Sets the markup to use as body for the section. If this is not set,
    * the section will not be rendered.
    * 
    * @param string|number|UI_Renderable_Interface $content
    * @return $this
    * @see startCapture()
    */
    public function setContent($content)
    {
        return $this->setProperty('content', toString($content));
    }

    public function getContent() : string
    {
        return strval($this->getProperty('content'));
    }
    
   /**
    * Limits the height of the body of the section 
    * to the specified pixel height. A height above
    * will display a scrollbar.
    * 
    * @param int $height
    * @return $this
    */
    public function setMaxBodyHeight(int $height)
    {
        return $this->setProperty('max-body-height', $height);
    }
    
   /**
    * Appends markup to the existing section content.
    * @param string|number|UI_Renderable_Interface $content
    * @return $this
    * @see prependContent()
    */
    public function appendContent($content)
    {
        return $this->setContent($this->getProperty('content').toString($content));
    }
    
   /**
    * Prepends content to the existing section content.
    * @param string|number|UI_Renderable_Interface $content
    * @return $this
    */
    public function prependContent($content)
    {
        return $this->setContent(toString($content).$this->getProperty('content'));
    }
    
   /**
    * Sets a section template property. Does not check if it
    * is a known property.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    protected function setProperty(string $name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
   /**
    * Retrieves a property value.
    * @param string $name
    * @return mixed
    */
    protected function getProperty(string $name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }    
        
        return null;
    }
    
    public function getMaxBodyHeight() : int
    {
        $height = $this->getProperty('max-body-height');

        if(!empty($height)) {
            return intval($height);
        }
        
        return 0;
    }
    
   /**
    * Renders the section's markup and returns it.
    * @return string
    */
    protected function _render()
    {
        if(!$this->isValid()) {
            return '';
        }

        // The stylesheet needs to be loaded, even if this section is not displayed.
        $this->ui->addStylesheet('ui-sections.css');

        // end capturing in case we're still capturing content
        $this->endCapture();

        $content = $this->renderContent();

        if(empty($content) && !$this->isVisibleIfEmpty()) {
            return '';
        }
        
        $params = $this->properties;
        $params['content'] = $content;
        $params['section'] = $this;

        return $this->page->renderTemplate(
            $this->templateName,
            $params
        );
    }

    private function renderContent() : string
    {
        $content = '';
        if(!empty($this->contents)) {
            foreach($this->contents as $renderable) {
                if($this->isLocked() && $renderable instanceof Application_LockableItem_Interface) {
                    $renderable->makeLockable();
                    $renderable->lock($this->lockReason);
                }
                $content .= $renderable->render();
            }
        }

        $content .= $this->getProperty('content');

        return $content;
    }
    
   /**
    * Whether the section has an abstract set.
    * @return boolean
    */
    public function hasAbstract() : bool
    {
        return $this->getAbstract() !== '';
    }
    
   /**
    * Whether the section has context buttons.
    * @return boolean
    */
    public function hasContextButtons() : bool
    {
        return !empty($this->contextButtons);
    }
    
    protected $capturing = false;
    
   /**
    * Starts output buffering to capture the content to use for the section's body.
    * @return $this
    * @see endCapture()
    */
    public function startCapture()
    {
        if(!$this->capturing) {
            $this->capturing = true;
            ob_start(); 
        }
        
        return $this;
    }
    
   /**
    * Stops the output buffering started with {@link startCapture()}. 
    * Note: this is done automatically whenever you call {@link render()} 
    * or {@link display()}.
    * 
    * @return $this
    */
    public function endCapture()
    {
        if($this->capturing) {
            $this->setContent(ob_get_clean());
            $this->capturing = false; 
        }

        return $this;
    }

    /**
     * Turns the section into an empty section with just an informational message.
     * The message is automatically set to not dismissable, and the message itself
     * is prepended with an information icon.
     *
     * @param string $message
     * @return UI_Page_Section
     */
    public function makeInfoMessage($message)
    {
        return $this->setContent(
            $this->page->renderInfoMessage(
                UI::icon()->information() . ' ' .
                $message,
                array(
                    'dismissable' => false
                )
            )
        );
    }
    
   /**
    * Makes the section collapsible.
    * 
    * NOTE: Requires a title to be set, or it will not work.
    * 
    * @param boolean $collapsed Whether the section should start collapsed.
    * @return $this
    * @see UI_Page_Section::makeStatic()
    */
    public function makeCollapsible($collapsed=false)
    {
        $this->setProperty('collapsible', true);
        $this->setProperty('collapsed', $collapsed);
        return $this;
    }
    
   /**
    * Sets the icon to use for the section. Note that if the 
    * section has no title, this is likely not to be shown.
    * 
    * @param UI_Icon $icon
    * @return $this
    */
    public function setIcon(UI_Icon $icon)
    {
        return $this->setProperty('icon', $icon);
    }
    
   /**
    * Retrieves the section's icon, if any.
    * @return NULL|UI_Icon
    */
    public function getIcon() : ?UI_Icon
    {
        return $this->getProperty('icon');
    }
    
   /**
    * Checks whether the section has an icon set.
    * @return boolean
    */
    public function hasIcon() : bool
    {
        return $this->hasProperty('icon');
    }
    
   /**
    * Checks whether the specified property has a non empty value set.
    * @param string $name
    * @return boolean
    */
    protected function hasProperty(string $name) : bool
    {
        $prop = $this->getProperty($name);
        return !empty($prop);
    }
    
   /**
    * Makes the section static, as in not collapsible. This is the
    * default behavior - call this after having made a section
    * collapsible with the {@link makeCollapsible} method.
    * 
    * @return $this
    * @see UI_Page_Section::makeCollapsible()
    */
    public function makeStatic()
    {
        $this->setProperty('collapsible', false);
        $this->setProperty('collapsed', false);
        return $this;
    }

    public function makeCompact()
    {
        return $this->setProperty('compact', true);
    }

    public function isCompact() : bool
    {
        return $this->getProperty('compact') === true;
    }

    /**
     * @return $this
     */
    public function expand()
    {
        return $this->makeCollapsible(false);
    }

    /**
     * @return $this
     */
    public function collapse()
    {
        return $this->makeCollapsible(true);
    }

    /**
     * @param bool $collapsed
     * @return $this
     */
    public function setCollapsed(bool $collapsed=true)
    {
        return $this->makeCollapsible($collapsed);
    }
    
    public function isExpanded()
    {
        if(!$this->isCollapsible()) {
            return true;
        }

        return $this->getProperty('collapsed') !== true;
    }
    
    public function isCollapsed() : bool
    {
        if(!$this->isCollapsible()) {
            return false;
        }

        if(isset($this->form) && $this->form->isSubmitted()){
            if(!$this->form->validate()) {
                return false;
            }
        }
        
        return $this->getProperty('collapsed') === true;
    }

    /**
     * Creates and adds a quick selector to the section, which
     * is shown to the right of the section title.
     *
     * @param string $id
     * @return UI_QuickSelector
     * @throws Application_Exception
     */
    public function addQuickSelector(string $id='') : UI_QuickSelector
    {
        $quick = UI::getInstance()->createQuickSelector($id);
        $this->setQuickSelector($quick);

        return $quick;
    }
    
   /**
    * Sets a previously created quick selector object to use
    * as quick selection within the section.
    * 
    * @param UI_QuickSelector $quick
    * @return $this
    */
    public function setQuickSelector(UI_QuickSelector $quick)
    {
        $this->quickSelector = $quick;
        return $this;
    }

    public function hasQuickSelector() : bool
    {
        return isset($this->quickSelector);
    }

    public function getQuickSelector() : ?UI_QuickSelector
    {
        return $this->quickSelector;
    }

    /**
     * Adds a context button to the section. These are usually
     * displayed around the title somewhere - it depends on the
     * template.
     *
     * @param UI_Button|UI_Bootstrap_ButtonDropdown|mixed $button
     * @return $this
     * @throws Application_Exception
     */
    public function addContextButton($button)
    {
        if(!$button instanceof UI_Button && !$button instanceof UI_Bootstrap_ButtonDropdown) {
            throw new Application_Exception(
                'Invalid button',
                'Only UI_Button or UI_Bootstrap_ButtonDropdown instances may be added.',
                self::ERROR_INVALID_CONTEXT_BUTTON    
            );
        } 
        
        $this->contextButtons[] = $button;

        return $this;
    }

    /**
     * @return array<int,UI_Bootstrap_ButtonDropdown|UI_Button>
     */
    public function getContextButtons() : array
    {
        return $this->contextButtons;
    }
    
    protected $contents = array();
    
   /**
    * Adds a new items selector content to the section, which
    * can be used to display a list of possible items to choose
    * from.
    * 
    * @return UI_ItemsSelector
    */
    public function addItemsSelector()
    {
        $selector = new UI_ItemsSelector();
        $this->addRenderable($selector);
        return $selector;
    }
    
   /**
    * Turns the section into a sidebar section, for use
    * in the sidebar.
    * 
    * @return $this
    */
    public function makeSidebar()
    {
        $this->setTemplateName('frame.sidebar.section');
        $this->removeClass('content-section');
        $this->addClass('sidebar-section');
        $this->setProperty('type', 'sidebar-section');
        
        return $this;
    }
    
   /**
    * Turns the section into a subsection made to be used
    * within a regular page section.
    * 
    * @return $this
    */
    public function makeSubsection()
    {
        $this->removeClass('content-section');
        $this->addClass('content-subsection');
        $this->setProperty('type', 'content-subsection');
        
        return $this;
    }
    
    public function getJSExpand()
    {
        if(!$this->isCollapsible()) {
            return '';
        }

        return "UI.ExpandSections('".$this->getGroup()."')";
    }
    
    public function getJSCollapse()
    {
        if(!$this->isCollapsible()) {
            return '';
        }
        
        return "UI.CollapseSections('".$this->getGroup()."')";
    }
    
    public function isCollapsible() : bool
    {
        return $this->getProperty('collapsible') === true;
    }
    
    public function addForm(UI_Form $form)
    {
        return $this->addRenderable($form);
    }
    
   /**
    * Sets a form instance to use as content of the section.
    * If the section is set to collapsible, it is expanded
    * automatically if the form has been submitted and is not
    * valid.
    * 
    * @param UI_Form $form
    * @return $this
    */
    public function setForm(UI_Form $form)
    {
        return $this->addRenderable($form);
    }
    
   /**
    * Adds a renderable content to the section's content area.
    * These will be rendered in the order they are added.
    * 
    * @param UI_Renderable_Interface $renderable
    */
    public function addRenderable(UI_Renderable_Interface $renderable)
    {
        $this->contents[] = $renderable;
        return $this;
    }
    
   /**
    * Adds a template to render as content in the section.
    * 
    * @param string $templateID
    * @param array $params
    * @return $this
    */
    public function addTemplate($templateID, $params=array())
    {
        return $this->addRenderable(
            $this->createContent('Template')
            ->setOption('variables', $params)
            ->setOption('templateID', $templateID)
        );
    }

   /**
    * Adds custom HTML code to the section.
    * 
    * @param string $html
    * @return $this
    */
    public function addHTML($html)
    {
        return $this->addRenderable(
            $this->createContent('HTML')
            ->setOption('html', $html)
        );
    }
    
   /**
    * Adds a separator between other contents.
    * @return $this
    */
    public function addSeparator()
    {
        return $this->addRenderable(
            $this->createContent('Separator')
        );
    }
    
    public function addHeading($title)
    {
        return $this->addRenderable(
            $this->createContent('Heading')
            ->setOption('title', $title)
        );
    }
    
    public function addSubsection()
    {
        $sub = $this->page->createSubsection();
        $this->addRenderable($sub);
        return $sub;
    }
    
   /**
    * Creates a section-specific content class.
    * 
    * @param string $type
    * @return UI_Page_Section_Content
    */
    protected function createContent($type)
    {
        $class = 'UI_Page_Section_Content_'.$type;
        
        return new $class($this);
    }
    
   /**
    * Disables the internal padding on the section's body,
    * making the content touch on all sides of the section's body.
    * 
    * @return $this
    */
    public function disablePadding()
    {
        return $this->addClass('nopadding');
    }
    
    public function enablePadding()
    {
        return $this->removeClass('nopadding');
    }

    public function isLockable()
    {
        return $this->lockable;
    }
    
    protected $locked = false;
    
    protected $lockReason = null;
    
   /**
    * {@inheritDoc}
    * @see Application_LockableItem_Interface::lock()
    * @return $this
    */
    public function lock($reason)
    {
        if($this->isLockable()) {
            $this->locked = true;
            $this->lockReason = $reason;
        }
        
        return $this;
    }
    
    public function getLockReason()
    {
        if($this->locked) {
            return $this->lockReason;
        }
        
        return '';
    }
    
   /**
    * {@inheritDoc}
    * @see Application_LockableItem_Interface::unlock()
    * @return $this
    */
    public function unlock()
    {
        $this->locked = false;
        return $this;
    }
    
    public function isLocked()
    {
        return $this->locked;
    }

    protected $lockable = false;
    
   /**
    * Makes the button lockable: it will automatically be disabled
    * if the administration screen is locked by the lockmanager.
    * 
    * @param bool $lockable
    * @return $this
    */
    public function makeLockable($lockable=true)
    {
        $this->lockable = true;
        return $this;
    }
    
   /**
    * @var UI_Page_Section_Tab[]
    */
    protected $tabs;
    
   /**
    * Adds a tab to the section, which is used to compartmentalize the section's contents.
    * 
    * @param string $name Unique name/alias to identify the tab by - not shown in the UI.
    * @param string $label Human readable label of the tab.
    * @return UI_Page_Section_Tab
    */
    public function addTab($name, $label)
    {
        if(!isset($this->tabs)) 
        {
            $this->tabs = array();
        }
        
        if(isset($this->tabs[$name])) {
            throw new Application_Exception(
                'Tab already exists',
                sprintf(
                    'The tab [%s] has been added previously, the same name may not be used again.',
                    $name
                ),
                self::ERROR_TAB_ALREADY_EXISTS
            );
        }
        
        $tab = new UI_Page_Section_Tab($this, $name, $label);
        $this->tabs[$name] = $tab;
        
        return $tab;
    }
    
   /**
    * Checks whether this section has tabs.
    * @return boolean
    */
    public function hasTabs()
    {
        return isset($this->tabs) && !empty($this->tabs);
    }
    
   /**
    * @return UI_Page_Section_Tab[]
    */
    public function getTabs()
    {
        if(empty($this->tabs)) {
            return array();
        }
        
        return $this->tabs;
    }
    
    public function isSeparator()
    {
        return false;
    }
    
    /**
     * @var UI_Page_Sidebar_ItemInterface|NULL
     */
    protected $previousSibling;
    
    /**
     * @var UI_Page_Sidebar_ItemInterface|NULL
     */
    protected $nextSibling;
    
    /**
     * Registers the position of the item in the sidebar. Called automatically
     * by the sidebar before it is rendered.
     *
     * @param UI_Page_Sidebar_ItemInterface $prev
     * @param UI_Page_Sidebar_ItemInterface $next
     * @return UI_Page_Sidebar_ItemInterface
     * @see UI_Page_Sidebar::getItems()
     */
    public function registerPosition(UI_Page_Sidebar_ItemInterface $prev=null, UI_Page_Sidebar_ItemInterface $next=null)
    {
        $this->previousSibling = $prev;
        $this->nextSibling = $next;
        return $this;
    }
    
    /**
     * Retrieves the previous item in the sidebar before this one, if any.
     * @return UI_Page_Sidebar_ItemInterface|NULL
     */
    public function getPreviousSibling()
    {
        return $this->previousSibling;
    }
    
    /**
     * Retrieves the next item in the sidebar after this one, if any.
     * @return UI_Page_Sidebar_ItemInterface|NULL
     */
    public function getNextSibling()
    {
        return $this->nextSibling;
    }
}