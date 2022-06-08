<?php
/**
 * File containing the {@link UI_Page_Section} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Section
 */

use Application\ClassFinder;
use Application\Exception\ClassFinderException;
use Application\Exception\ClassNotExistsException;
use Application\Exception\UnexpectedInstanceException;
use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use AppUtils\Traits_Classable;
use AppUtils\Interface_Classable;
use function AppUtils\parseNumber;

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
abstract class UI_Page_Section
    extends
        UI_Renderable
    implements
        UI_Interfaces_Conditional,
        Application_LockableItem_Interface,
        UI_Page_Sidebar_ItemInterface,
        Application_Interfaces_Iconizable,
        Interface_Classable,
        UI_Interfaces_StatusElementContainer
{
    use Traits_Classable;
    use UI_Traits_Conditional;
    use UI_Traits_StatusElementContainer;
    use Application_Traits_LockableStatus;
    use Application_Traits_LockableItem;
    
    public const ERROR_INVALID_CONTEXT_BUTTON = 511001;
    public const ERROR_TAB_ALREADY_EXISTS = 511002;
    
    protected string $templateName = 'frame.content.section';

    /**
     * @var array<int,UI_Button|UI_Bootstrap_ButtonDropdown>
     */
    protected array $contextButtons = array();
    
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

    private ?UI_QuickSelector $quickSelector = null;

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
    public function setTemplateName(string $templateName) : self
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
    public function setID(string $id) : self
    {
        return $this->setProperty('id', $id);
    }
    
   /**
    * Retrieves the section's ID attribute.
    * @return string
    */
    public function getID() : string
    {
        return (string)$this->getProperty('id');
    }

    /**
     * Optional. Sets the section's heading title.
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        return $this->setProperty('title', toString($title));
    }

    /**
     * @param string $anchor
     * @return $this
     */
    public function setAnchor(string $anchor) : self
    {
        return $this->setProperty('anchor', $anchor);
    }

    public function getAnchor() : string
    {
        return (string)$this->getProperty('anchor');
    }

   /**
    * Whether to skip the section rendering if its contents are empty.
    * @return boolean
    */
    public function isVisibleIfEmpty() : bool
    {
        return $this->getProperty('visible-if-empty') === true;
    }
    
   /**
    * Turns off the behavior that a section with empty
    * content is not displayed in the generated HTML.
    * This is mainly used to allow using a section
    * clientside, and fill it there. 
    * 
    * @param bool $visible
    * @return $this
    */
    public function setVisibleIfEmpty(bool $visible=true) : self
    {
        return $this->setProperty('visible-if-empty', $visible);
    }

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup(string $group) : self
    {
        return $this->setProperty('group', $group);
    }
    
    public function getGroup() : string
    {
        return (string)$this->getProperty('group');
    }
    
    public const BACKGROUND_TYPE_LIGHT = 'light';
    
   /**
    * Makes the section's background a solid color, with the specified
    * style. Use this in cases where the section should not have a transparent
    * background.
    * 
    * @param string $type
    * @return $this
    */
    public function makeSolidBackground(string $type) : self
    {
        return $this->addClass('solid-background-'.$type);
    }
    
   /**
    * Gives the section a light background color, as defined
    * in the CSS class "solid-background-light".
    * 
    * @return $this
    */
    public function makeLightBackground() : self
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
     * @throws UI_Exception
     */
    public function setTagline($text) : self
    {
        return $this->setProperty('tagline', toString($text));
    }

    public function getTagline() : string
    {
        return (string)$this->getProperty('tagline');
    }

    public function hasTagline() : bool
    {
        return $this->getTagline() !== '';
    }

    /**
     * Optional. Sets an abstract text that explains the contents of the section.
     * @param string|number|UI_Renderable_Interface $text
     * @return $this
     * @throws UI_Exception
     */
    public function setAbstract($text) : self
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
     * @throws UI_Exception
     * @see startCapture()
     */
    public function setContent($content) : self
    {
        return $this->setProperty('content', toString($content));
    }

    public function getContent() : string
    {
        return (string)$this->getProperty('content');
    }
    
   /**
    * Limits the height of the body of the section 
    * to the specified pixel height. A height above
    * will display a scrollbar.
    * 
    * @param int $height
    * @return $this
    */
    public function setMaxBodyHeight(int $height) : self
    {
        return $this->setProperty('max-body-height', $height);
    }

    /**
     * Appends markup to the existing section content.
     * @param string|number|UI_Renderable_Interface $content
     * @return $this
     * @throws UI_Exception
     * @see prependContent()
     */
    public function appendContent($content) : self
    {
        return $this->setContent($this->getProperty('content').toString($content));
    }

    /**
     * Prepends content to the existing section content.
     * @param string|number|UI_Renderable_Interface $content
     * @return $this
     * @throws UI_Exception
     */
    public function prependContent($content) : self
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
    protected function setProperty(string $name, $value) : self
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
   /**
    * Retrieves a property value.
    * @param string $name
    * @return mixed|NULL
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
        $height = parseNumber($this->getProperty('max-body-height'));

        if(!$height->isEmpty()) {
            return (int)$height->getNumber();
        }
        
        return 0;
    }

    /**
     * Renders the section's markup and returns it.
     * @return string
     * @throws UI_Themes_Exception
     */
    protected function _render() : string
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
        $locked = $this->isLocked();

        if(!empty($this->contents))
        {
            foreach($this->contents as $renderable)
            {
                if($locked && $renderable instanceof Application_LockableItem_Interface)
                {
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
    
    protected bool $capturing = false;

    /**
     * Starts output buffering to capture the content to use for the section's body.
     * @return $this
     * @throws OutputBuffering_Exception
     * @see endCapture()
     */
    public function startCapture() : self
    {
        if(!$this->capturing) {
            $this->capturing = true;
            OutputBuffering::start();
        }
        
        return $this;
    }

    /**
     * Stops the output buffering started with {@link startCapture()}.
     * Note: this is done automatically whenever you call {@link render()}
     * or {@link display()}.
     *
     * @return $this
     * @throws OutputBuffering_Exception
     * @throws UI_Exception
     */
    public function endCapture() : self
    {
        if($this->capturing) {
            $this->setContent(OutputBuffering::get());
            $this->capturing = false; 
        }

        return $this;
    }

    /**
     * Turns the section into an empty section with just an informational message.
     * The message is automatically set to not dismissible, and the message itself
     * is prepended with an information icon.
     *
     * @param string|int|float|UI_Renderable_Interface|NULL $message
     * @return $this
     * @throws UI_Exception
     */
    public function makeInfoMessage($message) : self
    {
        return $this->setContent(
            $this->page->createMessage($message)
                ->makeNotDismissable()
                ->makeInfo()
                ->enableIcon()
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
    public function makeCollapsible(bool $collapsed=false) : self
    {
        $this->setProperty('collapsible', true);
        $this->setProperty('collapsed', $collapsed);
        return $this;
    }
    
   /**
    * Sets the icon to use for the section. Note that if the 
    * section has no title, this is likely not to be shown.
    * 
    * @param UI_Icon|NULL $icon
    * @return $this
    */
    public function setIcon(?UI_Icon $icon) : self
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
    * Checks whether the specified property has a non-empty value set.
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
    public function makeStatic() : self
    {
        $this->setProperty('collapsible', false);
        $this->setProperty('collapsed', false);
        return $this;
    }

    /**
     * @return $this
     */
    public function makeCompact() : self
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
    public function expand() : self
    {
        return $this->makeCollapsible(false);
    }

    /**
     * @return $this
     */
    public function collapse() : self
    {
        return $this->makeCollapsible(true);
    }

    /**
     * @param bool $collapsed
     * @return $this
     */
    public function setCollapsed(bool $collapsed=true) : self
    {
        return $this->makeCollapsible($collapsed);
    }
    
    public function isExpanded() : bool
    {
        if(!$this->isCollapsible()) {
            return true;
        }

        return $this->getProperty('collapsed') !== true;
    }

    public function getForm() : ?UI_Form
    {
        foreach($this->contents as $content)
        {
            if($content instanceof UI_Form)
            {
                return $content;
            }
        }

        return null;
    }
    
    public function isCollapsed() : bool
    {
        if(!$this->isCollapsible())
        {
            return false;
        }

        $form = $this->getForm();

        if($form !== null && $form->isSubmitted() && !$form->isValid())
        {
            return false;
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
    public function setQuickSelector(UI_QuickSelector $quick) : self
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
    public function addContextButton($button) : self
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

    /**
     * @var UI_Renderable_Interface[]
     */
    protected array $contents = array();
    
   /**
    * Adds a new item selector content to the section, which
    * can be used to display a list of possible items to choose
    * from.
    * 
    * @return UI_ItemsSelector
    */
    public function addItemsSelector() : UI_ItemsSelector
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
    public function makeSidebar() : self
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
    public function makeSubsection() : self
    {
        $this->removeClass('content-section');
        $this->addClass('content-subsection');
        $this->setProperty('type', 'content-subsection');
        
        return $this;
    }
    
    public function getJSExpand() : string
    {
        if(!$this->isCollapsible()) {
            return '';
        }

        return "UI.ExpandSections('".$this->getGroup()."')";
    }
    
    public function getJSCollapse() : string
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

    /**
     * @param UI_Form $form
     * @return $this
     */
    public function addForm(UI_Form $form) : self
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
    public function setForm(UI_Form $form) : self
    {
        return $this->addRenderable($form);
    }
    
   /**
    * Adds a renderable content to the section's content area.
    * These will be rendered in the order they are added.
    * 
    * @param UI_Renderable_Interface $renderable
    * @return $this
    */
    public function addRenderable(UI_Renderable_Interface $renderable) : self
    {
        $this->contents[] = $renderable;
        return $this;
    }
    
   /**
    * Adds a template to render as content in the section.
    * 
    * @param string $templateID
    * @param array<string,mixed> $params
    * @return $this
    */
    public function addTemplate(string $templateID, array $params=array()) : self
    {
        return $this->addRenderable(
            $this->createContent('Template')
            ->setOption('variables', $params)
            ->setOption('templateID', $templateID)
        );
    }

   /**
    * Adds custom HTML to the section.
    * 
    * @param string $html
    * @return $this
    */
    public function addHTML(string $html) : self
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
    public function addSeparator() : self
    {
        return $this->addRenderable(
            $this->createContent('Separator')
        );
    }

    /**
     * @param string|int|float|UI_Renderable_Interface|NULL $title
     * @return $this
     */
    public function addHeading($title) : self
    {
        return $this->addRenderable(
            $this->createContent('Heading')
            ->setOption('title', $title)
        );
    }
    
    public function addSubsection() : UI_Page_Section
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
     *
     * @throws ClassNotExistsException
     * @throws ClassFinderException
     * @throws UnexpectedInstanceException
     */
    protected function createContent(string $type) : UI_Page_Section_Content
    {
        $class = ClassFinder::requireResolvedClass(UI_Page_Section_Content::class.'_'.$type);

        return ClassFinder::requireInstanceOf(
            UI_Page_Section_Content::class,
            new $class($this)
        );
    }
    
   /**
    * Disables the internal padding on the section's body,
    * making the content touch on all sides of the section's body.
    * 
    * @return $this
    */
    public function disablePadding() : self
    {
        return $this->addClass('nopadding');
    }
    
    public function enablePadding() : self
    {
        return $this->removeClass('nopadding');
    }

   /**
    * @var array<string,UI_Page_Section_Tab>
    */
    protected array $tabs = array();
    
   /**
    * Adds a tab to the section, which is used to compartmentalize the section's contents.
    * 
    * @param string $name Unique name/alias to identify the tab by - not shown in the UI.
    * @param string $label Human-readable label of the tab.
    * @return UI_Page_Section_Tab
    */
    public function addTab(string $name, string $label) : UI_Page_Section_Tab
    {
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
    public function hasTabs() : bool
    {
        return !empty($this->tabs);
    }
    
   /**
    * @return UI_Page_Section_Tab[]
    */
    public function getTabs() : array
    {
        return array_values($this->tabs);
    }
    
    public function isSeparator() : bool
    {
        return false;
    }
    
    protected ?UI_Page_Sidebar_ItemInterface $previousSibling = null;
    protected ?UI_Page_Sidebar_ItemInterface $nextSibling = null;

    /**
     * Registers the position of the item in the sidebar. Called automatically
     * by the sidebar before it is rendered.
     *
     * @param UI_Page_Sidebar_ItemInterface|null $prev
     * @param UI_Page_Sidebar_ItemInterface|null $next
     * @return $this
     * @see UI_Page_Sidebar::getItems()
     */
    public function registerPosition(UI_Page_Sidebar_ItemInterface $prev=null, UI_Page_Sidebar_ItemInterface $next=null) : self
    {
        $this->previousSibling = $prev;
        $this->nextSibling = $next;
        return $this;
    }
    
    /**
     * Retrieves the previous item in the sidebar before this one, if any.
     * @return UI_Page_Sidebar_ItemInterface|NULL
     */
    public function getPreviousSibling() : ?UI_Page_Sidebar_ItemInterface
    {
        return $this->previousSibling;
    }
    
    /**
     * Retrieves the next item in the sidebar after this one, if any.
     * @return UI_Page_Sidebar_ItemInterface|NULL
     */
    public function getNextSibling() : ?UI_Page_Sidebar_ItemInterface
    {
        return $this->nextSibling;
    }
}
