<?php
/**
 * File containing the {@link UI_Page} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_page
 */

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;

/**
 * Page utility class that offers common functionality
 * for pages. Note that only the application driver
 * knows (or rater is supposed to know) which pages
 * exist in the application. A page itself does not know
 * anything beyond its own ID.
 *
 * A page by default comes with helper objects to handle
 * the typical parts of a page, namely a header, footer
 * and sidebar.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see template_default_frame
 */
class UI_Page extends UI_Renderable
{
    public const ERROR_UNKNOWN_NAVIGATION = 45001;
    public const ERROR_PAGE_TITLE_CONTAINS_HTML = 45002;
    
    private string $id;
    private string $title = '';
    private UI_Page_Sidebar $sidebar;
    private UI_Page_Header $header;
    private UI_Page_Footer $footer;
    private string $content = '';
    private UI_Page_Breadcrumb $breadcrumb;
    private Application_User $user;
    protected string $consoleOutput = '';
    protected string $frame = 'frame';

    /**
     * @var array<string,UI_Page_Navigation>
     */
    protected array $navigations = array();

    /**
     * @var array<string,UI_Page_Breadcrumb>
     */
    protected array $breadcrumbs = array();

    /**
     * @var array<string,UI_QuickSelector>
     */
    protected array $quickSelectors = array();

    public function __construct(UI $ui, string $id)
    {
        $this->id = $id;
        $this->ui = $ui;
        $this->theme = $this->ui->getTheme();
        $this->renderer = $this->theme->createContentRenderer($ui);
        
        parent::__construct($this);
        
        $this->user = $this->driver->getUser();
        $this->sidebar = new UI_Page_Sidebar($this);
        $this->header = new UI_Page_Header($this);
        $this->footer = new UI_Page_Footer($this);
        $this->breadcrumb = $this->createBreadcrumb('main');
    }

    /**
     * @return Application_User
     */
    public function getUser() : Application_User
    {
        return $this->user;
    }
    
   /**
    * Sets the document title shown in the browser's toolbar.
    * 
    * NOTE: May not contain any HTML code.
    * 
    * @param string|number|UI_Renderable_Interface $title
    * @throws UI_Exception
    * @return UI_Page
    * 
    * @see UI_Page::ERROR_PAGE_TITLE_CONTAINS_HTML
    */
    public function setTitle($title) : UI_Page
    {
        $this->title = toString($title);
        
        if(ConvertHelper::isStringHTML($this->title))
        {
            throw new UI_Exception(
                'The page title may not contain HTML code.',
                sprintf(
                    'The title [%s] may not contain HTML.',
                    htmlspecialchars($title)
                ),
                self::ERROR_PAGE_TITLE_CONTAINS_HTML
            );
        }
        
        return $this;
    }

   /**
    * Retrieves the current page title.
    *  
    * @return string
    * 
    * @see UI_Page::resolveTitle()
    */
    public function getTitle() : string
    {
        return $this->title;
    }

    public function getID() : string
    {
        return $this->id;
    }

    /**
     * @return UI_Page_Sidebar
     */
    public function getSidebar() : UI_Page_Sidebar
    {
        return $this->sidebar;
    }

    /**
     * @return UI_Page_Header
     */
    public function getHeader() : UI_Page_Header
    {
        return $this->header;
    }

    /**
     * @return UI_Page_Footer
     */
    public function getFooter() : UI_Page_Footer
    {
        return $this->footer;
    }

    /**
     * Sets the HTML markup to use as content of the page.
     * Note that this is set automatically by the application
     * driver. Anything you set here manually gets replaced.
     *
     * @param string|int|float|UI_Renderable_Interface $content
     * @return $this
     * @throws UI_Exception
     */
    public function setContent($content) : self
    {
        $this->content = toString($content);
        
        return $this;
    }

    /**
     * Selects the frame to use to render the page.
     *
     * @param string $frameName
     * @return $this
     */
    public function selectFrame(string $frameName) : self
    {
        $this->frame = $frameName;
        return $this;
    }

    /**
     * Renders the HTML markup for the content of the page.
     * @return string
     * @throws UI_Themes_Exception
     */
    protected function _render() : string
    {
        return $this->createTemplate($this->frame)
            ->setVar('html.content', $this->content)
            ->render();
    }

    /**
     * Creates the markup for an error message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderErrorMessage(string $message, array $options = array()) : string
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_ERROR, $options);
    }

    /**
     * Creates the markup for an informational message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderInfoMessage(string $message, array $options = array()) : string
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_INFO, $options);
    }

    /**
     * Creates the markup for a success message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderSuccessMessage(string $message, array $options = array()) : string
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_SUCCESS, $options);
    }

    /**
     * Creates the markup for a warning message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderWarningMessage(string $message, array $options = array()) : string
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_WARNING, $options);
    }

    /**
     * Creates the markup for a message of the specified type and returns the
     * generated HTML code. You may use the options array to configure the
     * error message further.
     *
     * @param string $message
     * @param string $type
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderMessage(string $message, string $type, array $options = array()) : string
    {
        return $this->createMessage($message, $type, $options)->render();
    }

    /**
     * Creates a navigation renderer helper class instance
     * that can be used to create navigation menus. The
     * navigation ID is used to identify the navigation as
     * well as load the according template. The template must
     * be called "navigation.myid.php".
     *
     * @param string $navigationID
     * @return UI_Page_Navigation
     */
    public function createNavigation(string $navigationID) : UI_Page_Navigation
    {
        if (isset($this->navigations[$navigationID])) {
            return $this->navigations[$navigationID];
        }

        $nav = new UI_Page_Navigation($this, $navigationID);
        $this->navigations[$navigationID] = $nav;

        return $nav;
    }

    /**
     * Checks whether a navigation with the specified ID
     * exists.
     *
     * @param string $navigationID
     * @return bool
     */
    public function hasNavigation(string $navigationID) : bool
    {
        return isset($this->navigations[$navigationID]);
    }
    
    public function hasSubnavigation() : bool
    {
        return $this->hasNavigation('subnav');
    }

    /**
     * @param string $navigationID
     * @return UI_Page_Navigation
     * @throws UI_Exception
     */
    public function getNavigation(string $navigationID) : UI_Page_Navigation
    {
        if(isset($this->navigations[$navigationID])) {
            return $this->navigations[$navigationID];
        }
        
        throw new UI_Exception(
            'No such navigation',
            sprintf(
                'The navigation [%1$s] does not exist. Available navigations are [%2$s].',
                $navigationID,
                implode(', ', array_keys($this->navigations))
            ),
            self::ERROR_UNKNOWN_NAVIGATION
        );
    }
    
    public function getSubnavigation() : UI_Page_Navigation
    {
        return $this->getNavigation('subnav');
    }

    /**
     * Creates a breadcrumb navigation helper class instance that
     * can be used to build a breadcrumb navigation. The id is
     * used to identify the breadcrumb as well as to load the
     * according template. The template must be called
     * "navigation.breadcrumb.myid.php".
     *
     * @param string $breadcrumbID
     * @return UI_Page_Breadcrumb
     */
    public function createBreadcrumb(string $breadcrumbID) : UI_Page_Breadcrumb
    {
        if (isset($this->breadcrumbs[$breadcrumbID])) {
            return $this->breadcrumbs[$breadcrumbID];
        }

        $breadcrumb = new UI_Page_Breadcrumb($this, $breadcrumbID);
        $this->breadcrumbs[$breadcrumbID] = $breadcrumb;

        return $breadcrumb;
    }

    /**
     * Retrieve a permalink to the page with additional
     * optional request parameters. This is the raw
     * link to the page (without additional page-specific
     * request parameters). If you need a permalink, use
     * the {@see UI_Page::getPermalink()} method.
     *
     * @param array $params
     * @return string
     * @see UI_Page::getPermalink()
     */
    public function getURL(array $params = array()) : string
    {
        return Application_Driver::getInstance()->getPageURL($this, $params);
    }

    /**
     * Retrieves a list of parameters specific for this page,
     * dispatches this to the application driver (the driver
     * handles this kind of information). Returns an associative
     * array with param name => param value pairs.
     *
     * @return array
     */
    public function getPageParams() : array
    {
        return $this->ui->getApplication()->getDriver()->getPageParams($this);
    }

    /**
     * Retrieves a permalink to the page with all page-specific
     * request parameters
     */
    public function getPermalink() : string
    {
        return $this->getURL($this->getPageParams());
    }

    /**
     * Adds output to the console output, which is displayed
     * for developer users.
     *
     * @param string $markup
     * @return $this
     */
    public function addConsoleOutput(string $markup) : self
    {
        $this->consoleOutput .= $markup;
        return $this;
    }

    /**
     * Checks whether there is any console output to display.
     *
     * @return boolean
     */
    public function hasConsoleOutput() : bool
    {
        return !empty($this->consoleOutput);
    }

    /**
     * Returns the markup to show in the console.
     *
     * @return string
     */
    public function getConsoleOutput() : string
    {
        return $this->consoleOutput;
    }

    /**
     * @return UI_Page_Breadcrumb
     */
    public function getBreadcrumb() : UI_Page_Breadcrumb
    {
        return $this->breadcrumb;
    }

    /**
     * Creates a new page section object that can be used to
     * configure a section further than the template's renderSection
     * method allows.
     *
     * @param string $type The section type to create. This is one of the types from the UI/Page/Section subfolder (case sensitive).
     * @return UI_Page_Section
     */
    public function createSection(string $type = '') : UI_Page_Section
    {
        if (empty($type)) {
            $type = 'Default';
        }

        $class = UI_Page_Section::class.'_Type_' . $type;

        return new $class($this);
    }
    
   /**
    * Creates a step navigation helper class instance, which can
    * be used to render a navigation with incremental steps
    * like in a wizard or order process.
    * 
    * @return UI_Page_StepsNavigator
    */
    public function createStepsNavigator() : UI_Page_StepsNavigator
    {
        return new UI_Page_StepsNavigator($this);
    }
    
    public function addQuickSelector(string $selectorID) : UI_QuickSelector
    {
        $quick = UI::getInstance()->createQuickSelector($selectorID);
        $this->quickSelectors[$selectorID] = $quick;

        return $quick;
    }

    public function getQuickSelector($selectorID) : ?UI_QuickSelector
    {
        if (isset($this->quickSelectors[$selectorID])) {
            return $this->quickSelectors[$selectorID];
        }

        return null;
    }

    public function hasQuickSelector(string $selectorID) : bool
    {
        return isset($this->quickSelectors[$selectorID]);
    }

    /**
     * Creates the helper class that can be used to render a
     * page title for a revisionable instance. It gathers information
     * intelligently, for example adding a state badge if the
     * revisionable supports states.
     *
     * @param Application_Revisionable_Interface $revisionable
     * @return UI_Page_RevisionableTitle
     */
    public function createRevisionableTitle(Application_Revisionable_Interface $revisionable) : UI_Page_RevisionableTitle
    {
        return new UI_Page_RevisionableTitle($this, $revisionable);
    }

    /**
     * Creates a new page sidebar section object that can be used to
     * configure a section further than the template's renderSection
     * method allows.
     *
     * @param string $type The section type to create. This is one of the types from the UI/Page/Section subfolder (case sensitive).
     * @return UI_Page_Section
     */
    public function createSidebarSection(string $type='') : UI_Page_Section
    {
        $section = $this->createSection($type);
        $section->makeSidebar();
        return $section;
    }
    
   /**
    * Creates a new subsection, a section that is meant to be used
    * within another section. This is not compatible with sidebar
    * sections.
    * 
    * @param string $type
    * @return UI_Page_Section
    */
    public function createSubsection(string $type='') : UI_Page_Section
    {
        $section = $this->createSection($type);
        $section->makeSubsection();
        return $section;
    }

    /**
     * Creates a developer panel sidebar section instance.
     *
     * @return UI_Page_Section_Type_Developer
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function createDeveloperPanel() : UI_Page_Section_Type_Developer
    {
        return ClassHelper::requireObjectInstanceOf(
            UI_Page_Section_Type_Developer::class,
            $this->createSidebarSection('Developer')
        );
    }
    
   /**
    * Creates a new help instance, which is used to format
    * and manage help texts.
    * 
    * @return UI_Page_Help
    */
    public function createHelp() : UI_Page_Help
    {
        return new UI_Page_Help($this);
    }

    /**
     * @return Application_Admin_Area
     * @throws Application_Exception
     */
    public function getActiveArea() : Application_Admin_Area
    {
        return $this->driver->getActiveArea();
    }

    /**
     * Retrieves the currently active administration screen.
     *
     * @return Application_Admin_ScreenInterface
     * @throws Application_Exception
     */
    public function getActiveScreen() : Application_Admin_ScreenInterface
    {
        return $this->driver->getActiveScreen();
    }

    /**
     * Retrieves the lock manager instance used in the current
     * administration screen, if any.
     *
     * @return Application_LockManager|NULL
     * @throws Application_Exception
     */
    public function getLockManager() : ?Application_LockManager
    {
        $screen = $this->getActiveScreen();
        
        if($screen instanceof Application_Interfaces_Admin_LockableScreen) 
        {
            return $screen->getLockManager();
        }
        
        return null;
    }
    
    public function renderMessages() : string
    {
        if (!$this->ui->hasMessages() || $this->ui->isMessagesDeferred())
        {
            return '';
        }
        
        $messages = $this->ui->getMessages();
        $this->ui->clearMessages();

        return $this->renderTemplate(
            'frame.messages',
            array(
                'messages' => $messages
            )
        );
    }
    
    public function renderMaintenance() : string
    {
        return $this->renderTemplate('frame.maintenance');
    }
    
    public function renderConsole() : string
    {
        if($this->hasConsoleOutput())
        {
            return $this->renderTemplate('frame.dev-console');
        }

        return '';
    }
    
   /**
    * Checks whether the page has a context menu. This is
    * only possible when the page has a subnavigation, as
    * the context menu is integrated there.
    *
    * @return boolean
    */
    public function hasContextMenu() : bool
    {
        return $this->hasSubnavigation();
    }
    
   /**
    * Retrieves the page's subnavigation context menu.
    * 
    * NOTE: Check if it is available first.
    * 
    * @return UI_Bootstrap_DropdownMenu
    */
    public function getContextMenu() : UI_Bootstrap_DropdownMenu
    {
        return $this->getSubnavigation()->getContextMenu();
    }

   /**
    * Resolves the actual page title to use in the
    * document: this is the specified page title with
    * the application name appended.
    * 
    * @return string
    */
    public function resolveTitle() : string
    {
        $title = strip_tags($this->getTitle());
        
        if(empty($title))
        {
            $title = $this->driver->getAppNameShort();
        }
        else
        {
            $title .= ' - '.$this->driver->getAppNameShort();
        }
        
        return $title;
    }
}
