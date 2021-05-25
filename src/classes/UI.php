<?php
/**
 * File containing the {@link UI} class.
 * @package UserInterface
 * @see UI
 */

use function AppUtils\parseVariable;
use AppUtils\ConvertHelper;

/**
 * UI management class that handles display-related
 * functions like including javascript files and the
 * like.
 *
 * @package UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * 
 * @event FormCreated UI_Event_FormCreated
 */
class UI
{
    const ERROR_CANNOT_SELECT_DUMMY_INSTANCE = 39747001;
    const ERROR_NO_UI_INSTANCE_AVAILABLE_YET = 39747002;
    const ERROR_CANNOT_SELECT_PREVIOUS_INSTANCE = 39747003;
    const ERROR_INVALID_INCLUDE_URL = 39747004;
    const ERROR_NOT_A_RENDERABLE = 39747005;
    const ERROR_INVALID_BOOTSTRAP_ELEMENT = 39747006; 
    
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_ERROR= 'error';
    const MESSAGE_TYPE_WARNING ='warning';
    const MESSAGE_TYPE_INFO = 'info';
    
    /**
     * @var Application
     */
    private $app;

    /**
     * Stores javascript statements to run when the
     * page has loaded (included in the jQuery.ready()
     * function call).
     *
     * @var array
     * @see addJavascriptOnload()
     */
    private $onloadJS = array();

    /**
     * Stores javascript statements to run in the page
     * head.
     *
     * @var array
     * @see addJavascriptHead()
     */
    private $headJS = array();

    /**
     * Stores form-related error messages.
     * @var array
     * @see addFormError()
     * @see getFormErrors()
     */
    private $formErrors = array();

    /**
     * @var Application_Session
     */
    private $session;

   /**
    * @var string
    */
    private $instanceKey;

   /**
    * @var UI_Themes
    */
    private $themes;
    
   /**
    * @var UI_Themes_Theme
    */
    private $theme;
    
   /**
    * @var boolean
    */
    private $deferMessages = false;
    
   /**
    * @var boolean
    */
    private static $formsInitDone = false;
    
    /**
     * @var UI_MarkupEditor[]
     */
    private $markupEditorInstances = array();
    
   /**
    * @var UI_ResourceManager
    */
    private $resourceManager;
    
    /**
     * Note that the UI object is created automatically by
     * the application, you do not have to do this manually.
     *
     * @param string $instanceKey
     * @param Application $app
     */
    protected function __construct(string $instanceKey, Application $app)
    {
        $this->instanceKey = $instanceKey;
        $this->app = $app;
        $this->session = Application::getSession();
        $this->themes = new UI_Themes($this);
        $this->theme = $this->themes->getTheme();
        $this->resourceManager = new UI_ResourceManager($this);
    }
    
   /**
    * Retrieves the resource manager instance, which is used
    * to keep track of all clientside resources, like javascript
    * and stylesheet includes. 
    * 
    * @return UI_ResourceManager
    */
    public function getResourceManager() : UI_ResourceManager
    {
        return $this->resourceManager;
    }
    
   /**
    * Retrieves this UI object's instance key, which is unique
    * to each UI object.
    * 
    * @return string
    */
    public function getInstanceKey() : string
    {
        return $this->instanceKey;   
    }

    /**
     * Retrieves the currently selected UI instance. An UI instance
     * is created automatically be the application when it is instantiated,
     * after that this can be called to retrieve the active instance.
     * 
     * @return UI
     * @throws Application_Exception
     * @see selectDummyInstance()
     */
    public static function getInstance() : UI
    {
        if(empty(self::$instances)) {
            throw new Application_Exception(
                'No UI instance available',
                'Tried getting a UI instance, but none has been created yet.',
                self::ERROR_NO_UI_INSTANCE_AVAILABLE_YET    
            );
        }
        
        return self::$instances[self::$activeInstance];
    }
    
    private static $previousKey = null;
    
    private static $activeInstance = null;
    
   /**
    * @var array<string, UI>
    */
    private static $instances = array();
    
   /**
    * Creates a new UI instance for the specified application.
    * 
    * @param Application $app
    * @return UI
    */
    public static function createInstance(Application $app) : UI
    {
        $key = $app->getID();
        if(!isset(self::$instances[$key])) {
            self::$instances[$key] = new UI($key, $app);
        }
        
        self::$activeInstance = $key;
        return self::$instances[$key];
    }
    
   /**
    * Selects a dummy UI instance that can be used
    * in parallel to the main UI class.
    * 
    * This is useful for example when creating forms
    * to be sent via AJAX: to send only the javascript
    * required by the forms a dummy UI object is used
    * to capture only the javascript and styles added
    * by the form elements. 
    * 
    * @return UI
    * @throws Application_Exception
    */
    public static function selectDummyInstance()
    {
        if(empty(self::$instances)) {
            throw new Application_Exception(
                'No main UI instance created yet',
                'Tried selecting a dummy UI instance before the main instance has been created.',
                self::ERROR_CANNOT_SELECT_DUMMY_INSTANCE
            );
        }
        
        if(!isset(self::$instances['_dummy'])) {
            $key = key(self::$instances);
            $ui = new UI('_dummy', self::$instances[$key]->getApplication());
            $ui->setPage($ui->createPage('dummy'));
            self::$instances['_dummy'] = $ui;
        }

        if(self::$activeInstance != '_dummy') {
            self::$previousKey = self::$activeInstance;
            self::$activeInstance = '_dummy';
        }
        
        return self::getInstance();
    }
    
   /**
    * Restores the previously selected UI instance after
    * switching to another UI instance, for example using
    * the {@link selectDummyInstance()} method.
    * 
    * @param boolean $ignoreErrors Whether to ignore errors when no previous instance is present
    * @throws Application_Exception
    */
    public static function selectPreviousInstance($ignoreErrors=false)
    {
        if(!isset(self::$previousKey)) {
            if($ignoreErrors) {
                return;
            }
            throw new Application_Exception(
                'No previous UI instance available',
                'Tried selecting a previous instance, but no instance was switched yet.',
                self::ERROR_CANNOT_SELECT_PREVIOUS_INSTANCE    
            );
        }
        
        self::$activeInstance = self::$previousKey;
    }

    /**
     * @param string $id
     * @return UI_Page
     */
    public function createPage(string $id) : UI_Page
    {
        return new UI_Page($this, $id);
    }
    
   /**
    * Creates a new instance of the quick selector helper
    * class, which can be used to create quick selection 
    * UI elements for switching between items.
    * 
    * @return UI_QuickSelector
    */
    public function createQuickSelector(string $id='') : UI_QuickSelector
    {
        return new UI_QuickSelector($id);
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Adds a javascript file to include. This can be either
     * the filename of a file from the js/ subfolder, or a
     * full URL to an external file.
     *
     * If the configuration setting APP_JAVASCRIPT_MINIFIED is
     * set to true, for local file this will check if a minified
     * version is available. The file name for the minified
     * version is determined automatically by appending "-min"
     * to the file name.
     *
     * Example:
     *
     * myscript.js
     * myscript-min.js
     *
     * The priority parameter allows influencing the order in
     * which the scripts are added to the HTML source. A higher
     * priority will make the script move higher to the top.
     *
     * @param string $fileOrUrl
     * @param int $priority
     * @param bool $defer
     * @return UI_ClientResource_Javascript The client resource instance
     */
    public function addJavascript(string $fileOrUrl, int $priority = 0, bool $defer=false) : UI_ClientResource_Javascript
    {
        return $this->resourceManager->addJavascript($fileOrUrl, $priority, $defer);
    }
    
    public function addVendorJavascript(string $packageName, string $file, int $priority=0) : UI_ClientResource_Javascript
    {
        return $this->resourceManager->addVendorJavascript($packageName, $file, $priority);
    }

    public function addVendorStylesheet(string $packageName, string $file, int $priority=0) : UI_ClientResource_Stylesheet
    {
        return $this->resourceManager->addVendorStylesheet($packageName, $file, $priority);
    }
    
    
   /**
    * Retrieves the build-specific load key that is appended
    * to all javascript and stylesheet includes to force a 
    * refresh in browsers when deploying a new application
    * version.
    * 
    * @return string
    */
    public function getBuildKey() : string
    {
        return $this->loadKey;
    }

    public function hasBuildKey() : bool
    {
        return !empty($this->loadKey);
    }
    
    /**
     * Adds a javascript statement to run on when the page
     * has loaded using the jquery.ready() function.
     *
     * Example:
     *
     * addJavascriptOnload("alert('Hello World')");
     *
     * @param string $statement
     * @param boolean $avoidDuplicates Whether to ignore identical statements that have already been added
     */
    public function addJavascriptOnload($statement, $avoidDuplicates=false)
    {
        $statement = rtrim($statement, ';') . ';';
        
        if($avoidDuplicates && in_array($statement, $this->onloadJS)) {
            return;
    }

        $this->onloadJS[] = $statement;
    }

    /**
     * Adds a javascript statement to add to the head script tag.
     * The semicolon is added automatically, so you do not have to
     * include it.
     *
     * Example:
     *
     * addJavascriptHead("alert('Hello World')");
     *
     * @param string $statement
     */
    public function addJavascriptHead($statement, $addSemicolon=true)
    {
        if($addSemicolon) {
        	$statement = rtrim($statement, ';') . ';';
        }
        
        $this->headJS[] = $statement; 
    }

    /**
     * Adds a javascript variable to the head script tag. The variable
     * is automatically converted to the javascript equivalent.
     *
     * @param string $varName
     * @param mixed $varValue
     */
    public function addJavascriptHeadVariable($varName, $varValue)
    {
        $this->headJS[] = JSHelper::buildVariable($varName, $varValue);
    }

    /**
     * Builds and adds a javascript statement to the head script tag.
     * The first parameter is the javascript function to call, any
     * additional parameters are used as arguments for the javascript
     * function call. Variable types are automagically converted to
     * javascript types.
     *
     * Examples:
     *
     * // add an alert(); statement:
     * addJavascriptHeadStatement('alert');
     *
     * // add an alert('Alert text'); statement
     * addJavascriptHeadStatement('alert', 'Alert text');
     */
    public function addJavascriptHeadStatement()
    {
        $args = func_get_args();
        $statement = call_user_func_array(array('JSHelper', 'buildStatement'), $args);
        $this->headJS[] = $statement;
    }

   /**
    * Like {@link addJavascriptHeadStatement()}, but adds the statement
    * to the onload script block.
    * 
    * @see addJavascriptHeadStatement()
    */
    public function addJavascriptOnloadStatement()
    {
        $args = func_get_args();
        $statement = call_user_func_array(array('JSHelper', 'buildStatement'), $args);
        $this->addJavascriptOnload($statement);
    }
    
    /**
     * Adds a message to be displayed to the user. It is stored in
     * the session, so it will be displayed on the next request if
     * it cannot be shown during the current request (like after
     * saving a record followed by a redirect).
     *
     * @param string|number|UI_Renderable_Interface $message
     * @see hasMessages()
     * @see getMessages()
     * @see clearMessages()
     */
    public function addMessage($message, $type = UI::MESSAGE_TYPE_INFO)
    {
        $messages = $this->getMessages();
        $messages[] = array(
            'type' => $type,
            'text' => toString($message)
        );

        $this->session->setValue('application_messages', $messages);
    }

   /**
    * @param string|number|UI_Renderable_Interface $message
    */
    public function addSuccessMessage($message)
    {
        $this->addMessage($message, UI::MESSAGE_TYPE_SUCCESS);
    }

   /**
    * @param string|number|UI_Renderable_Interface $message
    */
    public function addErrorMessage($message)
    {
        $this->addMessage($message, UI::MESSAGE_TYPE_ERROR);
    }

   /**
    * @param string|number|UI_Renderable_Interface $message
    */
    public function addInfoMessage($message)
    {
        $this->addMessage($message, UI::MESSAGE_TYPE_INFO);
    }

   /**
    * @param string|number|UI_Renderable_Interface $message
    */
    public function addWarningMessage($message)
    {
        $this->addMessage($message, UI::MESSAGE_TYPE_WARNING);
    }
    
    /**
     * Checks if any user messages are present.
     * @return boolean
     */
    public function hasMessages()
    {
        $messages = $this->getMessages();

        return !empty($messages);
    }

    /**
     * Sets messages to be deferred to the next request. No messages
     * will be cleared this request, and any new ones will be added
     * to the queue.
     *
     * @return UI
     */
    public function deferMessages() : UI
    {
        $this->deferMessages = true;
        
        return $this;
    }

    /**
     * Checks whether messages are currently set to be deferred.
     *
     * @return boolean
     * @since 3.3.10
     */
    public function isMessagesDeferred() : bool
    {
        return $this->deferMessages;
    }

    /**
     * Retrieves all user messages that are present as
     * an indexed array with on message per entry in the
     * order that they were added.
     *
     * Messages are not cleared automatically, you have
     * to clear them using the {@link clearMessages()}
     * method.
     *
     * @return array
     * @see addMessage()
     * @see hasMessages()
     * @see clearMessages()
     */
    public function getMessages()
    {
        $messages = $this->session->getValue('application_messages', array());
        if (!is_array($messages)) {
            $messages = array();
        }

        return $messages;
    }

    /**
     * Clears all messages.
     *
     * @see addMessage()
     * @see hasMessages()
     * @see getMessages()
     */
    public function clearMessages() : UI
    {
        $this->session->setValue('application_messages', array());
        
        return $this;
    }

    /**
     * Creates a new data grid object that can be used to display
     * a list of items with added functionality like multiple
     * selection and the like.
     *
     * @param string $id
     * @param boolean $allowDuplicateID Allow using the same grid ID more than once?
     * @return UI_DataGrid
     */
    public function createDataGrid($id, $allowDuplicateID=false) : UI_DataGrid
    {
        return new UI_DataGrid($this, $id, $allowDuplicateID);
    }

    private static function initForms() : void
    {
        if (self::$formsInitDone) 
        {
            return;
        }

        // this custom rule is required because a radio group does not
        // have a text-based value: it has an array value that looks like
        // this in this case:
        //
        // array(
        //     'limit' => array(
        //         'catmode' => 'any'
        //     )
        // )
        //
        // The comparison rule bundled with quickform does not allow
        // comparing arrays, as all values are either converted to
        // string with strval or to floats. The equals rule simply
        // compares two variables using the === operator without
        // modifying them, making it possible to check for the exact
        // array value.
        HTML_QuickForm2_Factory::registerRule(
            'equals',
            'HTML_QuickForm2_Rule_Equals',
            null
        );
        
        self::$formsInitDone = true;
    }

    /**
     * Creates a new form object used as wrapper around the HTML_QuickForm2
     * object to make handling forms easier within the application.
     *
     * @param string $id
     * @return UI_Form
     * @see createGetForm()
     */
    public function createForm($id, $defaultData = array()) : UI_Form
    {
        self::initForms();
        $form = new UI_Form($this, $id, 'post', $defaultData);

        if(Application_EventHandler::hasListener('FormCreated')) {
            Application_EventHandler::trigger('FormCreated', array($form), 'UI_Event_FormCreated');
        }

        return $form;
    }

    /**
     * Creates a new form object that gets submitted via get instead
     * of the default post method.
     *
     * @param string $id
     * @param array $defaultData
     * @return UI_Form
     */
    public function createGetForm($id, $defaultData = array()) : UI_Form
    {
        self::initForms();

        return new UI_Form($this, $id, 'get', $defaultData);
    }

    /**
     * @var UI_Page
     */
    protected $page;

    /**
     * Sets the current page object; this is done automatically by the
     * application on startup.
     *
     * @param UI_Page $page
     */
    public function setPage(UI_Page $page)
    {
        if (isset($this->page)) {
            throw new Exception('The page may only be set once at startup');
        }

        $this->page = $page;
    }
    
    public function hasPage()
    {
        return isset($this->page);
    }

    /**
     * Retrieves the current page object.
     *
     * @throws Exception
     * @return UI_Page
     */
    public function getPage()
    {
        if (!isset($this->page)) {
            $this->page = new UI_Page($this, 'dummy');
        }

        return $this->page;
    }

    public function addStylesheet(string $fileOrUrl, string $media = 'all', int $priority = 0) : UI_ClientResource_Stylesheet
    {
        return $this->resourceManager->addStylesheet($fileOrUrl, $media, $priority);
    }
    
   /**
    * Adds a javascript or stylesheet to include clientside.
    * 
    * @param string $fileOrURL
    * @throws Application_Exception
    * @return UI_ClientResource
    * 
    * @see UI::addStylesheet()
    * @see UI::addJavascript()
    */
    public function addResource(string $fileOrURL) : UI_ClientResource
    {
        return $this->resourceManager->addResource($fileOrURL);
    }

    public function renderHeadIncludes()
    {
        $html = '';

        if(!empty($this->markupEditorInstances)) {
            foreach($this->markupEditorInstances as $editor) {
                $editor->start();
            }
        }
        
        $html .= $this->resourceManager->renderIncludes();

        if(empty($this->headJS) && empty($this->onloadJS)) {
           return $html; 
        }
        
        $html .=
        '<script>' . PHP_EOL;
            foreach ($this->headJS as $statement) {
                $html .= $statement . PHP_EOL;
            }
            
            if(!empty($this->onloadJS)) {
                $html .=
                   '$(document).ready(' . PHP_EOL .
                    'function() {' . PHP_EOL;
                        foreach ($this->onloadJS as $statement) {
                            $html .=      $statement . PHP_EOL;
                        }
                        $html .=
                    '}' . PHP_EOL .
               ');' . PHP_EOL;
            }
            $html .=
        '</script>' . PHP_EOL;

        $this->resetIncludes();
            
        return $html;
    }
    
    protected function resetIncludes()
    {
        $this->headJS = array();
        $this->onloadJS = array();
        $this->markupEditorInstances = array();
        $this->resourceManager = new UI_ResourceManager($this);
    }

    /**
     * @return UI_Bootstrap_ButtonDropdown
     */
    public function createButtonDropdown($label='') : UI_Bootstrap_ButtonDropdown
    {
        return new UI_Bootstrap_ButtonDropdown($this);
    }
    
   /**
    * @param string $label
    * @return UI_Bootstrap_BadgeDropdown
    */
    public function createBadgeDropdown($label='') : UI_Bootstrap_BadgeDropdown
    {
        $dropdown = new UI_Bootstrap_BadgeDropdown($this);
        $dropdown->setLabel($label);
        
        return $dropdown;
    }
    
   /**
    * @param string $label
    * @param string $url
    * @return UI_Bootstrap_Anchor
    */
    public function createAnchor($label='', $url='') : UI_Bootstrap_Anchor
    {
        $anchor = new UI_Bootstrap_Anchor($this);
        $anchor->setLabel($label);
        $anchor->setHref($url);
        
        return $anchor;
    }
    
   /**
    * Creates and returns a new button group helper instance,
    * which can be used to group buttons together.
    * 
    * @return UI_Bootstrap_ButtonGroup
    */
    public function createButtonGroup() : UI_Bootstrap_ButtonGroup
    {
        return new UI_Bootstrap_ButtonGroup($this);
    }
    
   /**
    * Creates and returns a big selection instance, which
    * is used to let the user select from a prominent list
    * of items.
    * 
    * @return UI_Bootstrap_BigSelection
    */    
    public function createBigSelection() : UI_Bootstrap_BigSelection
    {
        return new UI_Bootstrap_BigSelection($this);
    }
    
   /**
    * Creates a new tabs element.
    * 
    * @param string $name
    * @return UI_Bootstrap_Tabs
    */
    public function createTabs(string $name='') : UI_Bootstrap_Tabs
    {
        $tabs = new UI_Bootstrap_Tabs($this);
        
        if(!empty($name)) 
        {
            $tabs->setName($name);
        }
        
        return $tabs;
    }

    public function createDropdownAnchor(string $label) : UI_Bootstrap_DropdownAnchor
    {
        $dropdown = new UI_Bootstrap_DropdownAnchor($this);
        $dropdown->setLabel($label);

        return $dropdown;
    }

    /**
     * @return UI_Bootstrap_DropdownMenu
     */
    public function createDropdownMenu() : UI_Bootstrap_DropdownMenu
    {
        return new UI_Bootstrap_DropdownMenu($this);
    }

   /**
    * @param string $title
    * @return UI_Bootstrap_DropdownHeader
    */
    public function createDropdownHeader(string $title='') : UI_Bootstrap_DropdownHeader
    {
        $header = new UI_Bootstrap_DropdownHeader($this);
        $header->setTitle($title);
        
        return $header;
    }
    
   /**
    * @param string $content
    * @return UI_Bootstrap_DropdownStatic
    */
    public function createDropdownStatic(string $content) : UI_Bootstrap_DropdownStatic
    {
        $static = new UI_Bootstrap_DropdownStatic($this);
        $static->setContent($content);
        
        return $static;
    }
    
   /**
    * @param string $title
    * @return UI_Bootstrap_DropdownSubmenu
    */
    public function createDropdownSubmenu(string $title='') : UI_Bootstrap_DropdownSubmenu
    {
        $menu = new UI_Bootstrap_DropdownSubmenu($this);
        $menu->setTitle($title);
        
        return $menu;
    }

    /**
     * Adds output to the console output, which is displayed
     * for developer users.
     *
     * @since 3.3.5
     * @param string $markup
     */
    public function addConsoleOutput($markup)
    {
        $this->page->addConsoleOutput($markup);
    }

    /**
     * Creates and returns a new UI icon object.
     * @return UI_Icon
     */
    public static function icon()
    {
        return new UI_Icon();
    }
    
   /**
    * Creates an returns a new UI button object.
    * Use the button's API to configure its looks 
    * and functions. It supports string conversion.
    * 
    * @param string $label
    * @return UI_Button
    */
    public static function button(string $label='') : UI_Button
    {
    	return new UI_Button($label);
    }

   /**
    * @var string
    */
    protected $loadKey = '';

    /**
     * Sets the key to add to all scripts to make sure they are
     * refreshed on the client when the key changes. Note that this
     * has to set before any scripts are added to make sure it
     * is used everywhere.
     *
     * @param string $key
     * @since 3.3.10
     */
    public function setIncludesLoadKey(string $key) : void
    {
        $this->loadKey = $key;
    }

    /**
     * Adds clientside support for adding progress bars to the page
     * using the ProgressBar class.
     *
     * @since 3.3.11
     */
    public function addProgressBar()
    {
        $this->addJavascript('ui/progressbar.js');
        $this->addJavascript('dialog/progressbar.js');
        $this->addStylesheet('ui-progressbar.css');
    }
    
    public function addBootstrap()
    {
        $this->addStylesheet('bootstrap.min.css', 'all', 8000);
        $this->addJavascript('bootstrap.min.js', 8800);
    }
    
    const FONT_AWESOME_URL = 'https://use.fontawesome.com/releases/v5.8.1/css/all.css';
    
    public function addFontAwesome()
    {
        $this->addStylesheet(self::FONT_AWESOME_URL);
        
        // the JS SVG version does not work yet: JS handlers on the icons
        // do not work. See here for information and possible fixes:
        // https://fontawesome.com/how-to-use/svg-with-js#with-jquery
        //$this->addJavascript('https://use.fontawesome.com/releases/v5.0.8/js/all.js', null, true);
    }
    
    public function addJqueryUI()
    {
        $this->addStylesheet('jquery-ui.min.css', 'screen', 9000);
        $this->addJavascript('jquery-ui.min.js', 8900);
        $this->addJavascript('jquery-ui-timepicker.js', 8600);
    }
    
    public function addJquery()
    {
        $this->addJavascript('jquery.min.js', 9000);
    }
    
    public function createPropertiesGrid(string $id='') : UI_PropertiesGrid
    {
        return new UI_PropertiesGrid($this->getPage(), $id);
    }
    
   /**
    * Adds a redactor UI element.
    * 
    * @param HTML_QuickForm2_Element $element
    * @param Application_Countries_Country $country
    * @return UI_MarkupEditor_Redactor
    */
    public function addRedactor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_Redactor
    {
        return ensureType(
            UI_MarkupEditor_Redactor::class,
            $this->addMarkupEditor('Redactor', $element, $country)
        );
    }
    
   /**
    * Creates an instance of a markup editor helper class.
    * This includes all the necessary clientside includes, but
    * also pre-configures matching WYSIYWG elements on page load.
    * 
    * @param string $id The Markup editor ID, e.g. "Redactor", "Quill"
    * @param HTML_QuickForm2_Element $element The element the editor should be tied to
    * @param Application_Countries_Country $country The country for language of the content being edited
    * @return UI_MarkupEditor
    */
    public function addMarkupEditor(string $id, HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor
    {
        $class = 'UI_MarkupEditor_'.$id;
        
        $redactor = new $class($this, $element, $country);
        
        $this->markupEditorInstances[] = $redactor;
        return $redactor;
    }
    
   /**
    * Creates a new Badge UI element and returns it. These can
    * be converted to strings, so they can be inserted directly
    * into any content strings.
    * 
    * @param string $label
    * @return UI_Badge
    */
    public static function badge($label)
    {
        return new UI_Badge($label);
    }

   /**
    * Creates a new Label UI element and returns it. These can
    * be converted to strings, so they can be inserted directly
    * into any content strings.
    * 
    * @param string $label
    * @return UI_Label
    */
    public static function label($label)
    {
        return new UI_Label($label);
    }
    
    public static function printBacktrace()
    {
        $trace = debug_backtrace();
        array_shift($trace); // remove own call
        $trace = array_reverse($trace);
        
        $html =
        '<style>'.
            '.backtrace{padding:8px;background:#fff;margin:13px;border:solid 1px #ccc;border-radius:4px;}'.
            '.backtrace TD, .backtrace TH{padding:3px 6px;color:#454545;font-family:monospace;}'.
        '</style>'.
        '<div class="backtrace">'.
            '<table>'.
                '<thead>'.
                    '<th style="text-align:right">File</th>'.
                    '<th style="text-align:right">Line</th>'.
                    '<th style="text-align:left">Call</th>'.
                '</thead>'.
                '<tbody>';
                    foreach($trace as $entry) {
                        $call = $entry['function'];
                        if(isset($entry['class'])) {
                            $call = $entry['class'].$entry['type'].$entry['function'];
}
                        
                        $args = array();
                        foreach($entry['args'] as $arg) {
                            $type = gettype($arg);
                            switch($type) {
                                case 'string':
                                    $args[] = '"'.htmlspecialchars(trim($arg), ENT_QUOTES, 'UTF-8').'"';
                                    break;
                            }
                        }
                        
                        $call .= '('.implode(', ', $args).')';
                        
                        $html .=
                        '<tr>'.
                            '<td style="text-align:right">'.AppUtils\FileHelper::relativizePath($entry['file'], APP_ROOT).'</td>'.
                            '<td style="text-align:right">'.$entry['line'].'</td>'.
                            '<td>'.$call.'</td>'.
                        '</tr>';
                    }
                    $html .=
                '</tbody>'.
            '</table>'.
        '</div>';
                
        echo $html;
    }
    
   /**
    * @param string $type
    * @return UI_Page_Section
    */
    public function createSection(string $type='') : UI_Page_Section
    {
        return $this->getPage()->createSection($type);
    }

   /**
    * Adds support for clientside datagrid building with
    * the UI_DataGrid classes.
    * 
    * @return UI
    */    
    public function addDataGridSupport()
    {
        UI_DataGrid::addClientSupport();
        
        return $this;
    }

   /**
    * Adds support for the screened dialogs.
    * 
    * @return UI
    */
    public function addScreenedDialogs() : UI
    {
        $this->addJavascript('dialog/screened.js');
        
        return $this;
    }
    
   /**
    * @return UI_Themes_Theme
    */
    public function getTheme() : UI_Themes_Theme
    {
        return $this->themes->getTheme();
    }
    
   /**
    * @var integer
    */
    protected static $bootstrapVersion = 2;
    
    public static function selectBootstrap4() : void
    {
        self::$bootstrapVersion = 4;
    }
    
   /**
    * @return integer
    */
    public static function getBoostrapVersion() : int
    {
        return self::$bootstrapVersion;
    }
    
    public static function isBootstrap4() : bool
    {
        return self::getBoostrapVersion() === 4;
    }

    /**
     * Creates a new UI message instance and returns it.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string $type
     * @param array $options
     * @return UI_Message
     */
    public function createMessage($message, $type=UI::MESSAGE_TYPE_INFO, $options=array()) : UI_Message
    {
        return new UI_Message($this, $message, $type, $options);
    }
    
   /**
    * Creates a new template instance for the specified template ID.
    * 
    * @param string $templateID
    * @return UI_Page_Template
    */
    public function createTemplate(string $templateID) : UI_Page_Template
    {
        return $this->page->createTemplate($templateID);
    }
    
    public static function string() : UI_StringBuilder
    {
        return new UI_StringBuilder();
    }
    
   /**
    * Requires the subject to be a scalar value, or an object instance of the renderable interface.
    * 
    * @param mixed|UI_Renderable_Interface $subject
    * @throws UI_Exception
    * @return string|number|UI_Renderable_Interface
    * 
    * @see UI::ERROR_NOT_A_RENDERABLE
    */
    public static function requireRenderable($subject)
    {
        if(is_scalar($subject) || $subject instanceof UI_Renderable_Interface) {
            return $subject;
        }
        
        throw new UI_Exception(
            'Not a renderable',
            sprintf(
                'The subject is not a string or renderable object implementing the renderable interface: [%s].',
                parseVariable($subject)
            ),
            self::ERROR_NOT_A_RENDERABLE
        );
    }
    
    public function createBootstrap(string $type) : UI_Interfaces_Bootstrap
    {
        $class = 'UI_Bootstrap_'.$type;
        
        $instance = new $class($this);
        
        if($instance instanceof UI_Interfaces_Bootstrap)
        {
            return $instance;
        }
        
        throw new Application_Exception(
            'Invalid child element',
            sprintf('No bootstrap element [%s] found.', $class),
            self::ERROR_INVALID_BOOTSTRAP_ELEMENT
        );
    }

   /**
    * Retrieves a list of all supported markup editors.
    * 
    * @return UI_MarkupEditorInfo[]
    */
    public function getMarkupEditors() : array
    {
        return array(
            new UI_MarkupEditorInfo('Redactor', UI_MarkupEditor_Redactor::getLabel()),
            new UI_MarkupEditorInfo('CKEditor', UI_MarkupEditor_CKEditor::getLabel())
        );
    }
    
    public function getDefaultMarkupEditor() : UI_MarkupEditorInfo
    {
        $name = Application_Driver::getSetting('MarkupEditorID');

        $editors = $this->getMarkupEditors();
        
        foreach($editors as $editor)
        {
            if($editor->getID() === $name)
            {
                return $editor;
            }
        }
        
        return array_shift($editors);
    }
    
    public static function isJavascriptMinified() : bool
    {
        return boot_constant('APP_JAVASCRIPT_MINIFIED') === true;
    }
}
