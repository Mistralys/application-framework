<?php
/**
 * File containing the {@link UI} class.
 * @package UserInterface
 * @see UI
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Application;
use Application\ConfigSettings\BaseConfigRegistry;
use Application\Exception\UnexpectedInstanceException;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper_Exception;
use AppUtils\FileHelper;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OutputBuffering;
use AppUtils\PaginationHelper;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;
use UI\ClientResourceCollection;
use UI\PaginationRenderer;
use UI\SystemHint;
use UI\TooltipInfo;
use UI\Tree\TreeNode;
use UI\Tree\TreeRenderer;
use function AppUtils\parseVariable;

/**
 * UI management class that handles display-related
 * functions like including JavaScript files and the
 * like.
 *
 * @package UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * 
 * @event FormCreated UI_Event_FormCreated
 */
class UI
{
    public const int ERROR_CANNOT_SELECT_INSTANCE_BEFORE_MAIN = 39747001;
    public const int ERROR_NO_UI_INSTANCE_AVAILABLE_YET = 39747002;
    public const int ERROR_CANNOT_SELECT_PREVIOUS_INSTANCE = 39747003;
    public const int ERROR_NOT_A_RENDERABLE = 39747005;
    public const int ERROR_CANNOT_SET_PAGE_INSTANCE_AGAIN = 39747007;

    public const string MESSAGE_TYPE_SUCCESS = 'success';
    public const string MESSAGE_TYPE_ERROR= 'error';
    public const string MESSAGE_TYPE_WARNING ='warning';
    public const string MESSAGE_TYPE_WARNING_XL ='warning-xl';
    public const string MESSAGE_TYPE_INFO = 'info';

    private const string SESSION_VAR_APP_MESSAGES = 'application_messages';
    public const string EVENT_PAGE_RENDERED = 'pageRendered';
    public const string APP_INSTANCE_PREFIX = 'app-';

    private Application $app;
    private Application_Session $session;
    private string $instanceKey;
    private UI_Themes $themes;
    private bool $deferMessages = false;
    private static bool $formsInitDone = false;
    private UI_ResourceManager $resourceManager;
    private ?UI_Page $page = null;
    private string $loadKey = '';
    private static int $bootstrapVersion = 2;

    /**
     * Stores JavaScript statements to run when the
     * page has loaded (included in the jQuery.ready()
     * function call).
     *
     * @var string[]
     * @see addJavascriptOnload()
     */
    private array $onloadJS = array();

    /**
     * Stores JavaScript statements to run in the page
     * head.
     *
     * @var string[]
     * @see addJavascriptHead()
     */
    private array $headJS = array();

    /**
     * @var UI_MarkupEditor[]
     */
    private array $markupEditorInstances = array();

    /**
     * Note that the UI object is created automatically by
     * the application. You do not have to do this manually.
     *
     * @param string $instanceKey
     * @param Application $app
     * @throws Application_Exception
     */
    protected function __construct(string $instanceKey, Application $app)
    {
        $this->instanceKey = $instanceKey;
        $this->app = $app;
        $this->session = Application::getSession();
        $this->themes = new UI_Themes($this);
        $this->resourceManager = new UI_ResourceManager($this);
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
     * Retrieves the currently selected UI instance. A UI instance
     * is created automatically be the application when it is instantiated,
     * after that this can be called to retrieve the active instance.
     *
     * @see self::selectInstance()
     *
     * @return UI
     * @throws UI_Exception
     *
     */
    public static function getInstance() : UI
    {
        if(self::$activeInstanceID !== null && isset(self::$instances[self::$activeInstanceID]))
        {
            return self::$instances[self::$activeInstanceID];
        }

        throw new UI_Exception(
            'No UI instance available',
            'Tried getting a UI instance, but none has been created yet.',
            self::ERROR_NO_UI_INSTANCE_AVAILABLE_YET
        );
    }

    private static ?string $previousKey = null;
    private static ?string $activeInstanceID = null;
    
   /**
    * @var array<string,UI>
    */
    private static array $instances = array();
    
   /**
    * Creates a new UI instance for the specified application.
    * 
    * @param Application $app
    * @return UI
    */
    public static function createInstance(Application $app) : UI
    {
        $key = self::APP_INSTANCE_PREFIX .$app->getID();
        if(!isset(self::$instances[$key])) {
            self::$instances[$key] = new UI($key, $app);
        }
        
        self::$activeInstanceID = $key;
        return self::$instances[$key];
    }
    
    /**
     * Selects a UI instance that can be used in parallel
     * to the main UI instance.
     *
     * Any JavaScript or styles added to this instance
     * will only be included when the instance is used,
     * and will not be available in the main instance.
     *
     * One use-case is for client-side forms: They use
     * a dedicated UI instance to collect the necessary
     * JavaScript and includes.
     *
     * @param string $instanceName
     * @return UI
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public static function selectInstance(string $instanceName) : UI
    {
        if(empty(self::$instances)) {
            throw new UI_Exception(
                'No main UI instance created yet',
                'Tried selecting a UI instance before the main instance has been created.',
                self::ERROR_CANNOT_SELECT_INSTANCE_BEFORE_MAIN
            );
        }

        if(!isset(self::$instances[$instanceName])) {
            $ui = new UI($instanceName, AppFactory::createDriver()->getApplication());
            $ui->setPage($ui->createPage('ui-stub-'.$instanceName));
            self::$instances[$instanceName] = $ui;
        }

        if(self::$activeInstanceID !== $instanceName)
        {
            self::$previousKey = self::$activeInstanceID;
            self::$activeInstanceID = $instanceName;
        }

        return self::getInstance();
    }

    public static function selectDefaultInstance() : void
    {
        self::$activeInstanceID = self::APP_INSTANCE_PREFIX.AppFactory::createDriver()->getApplication()->getID();
    }

   /**
    * Restores the previously selected UI instance after
    * switching to another UI instance using the
    * {@see self::selectInstance()} method.
    *
    * @param boolean $ignoreErrors Whether to ignore errors when no previous instance is present
    * @throws Application_Exception
    */
    public static function selectPreviousInstance(bool $ignoreErrors=false) : void
    {
        if(isset(self::$previousKey))
        {
            self::$activeInstanceID = self::$previousKey;
            return;
        }

        if($ignoreErrors)
        {
            return;
        }

        throw new UI_Exception(
            'No previous UI instance available',
            'Tried selecting a previous instance, but no instance was switched yet.',
            self::ERROR_CANNOT_SELECT_PREVIOUS_INSTANCE
        );
    }

    /**
     * @return Application
     */
    public function getApplication() : Application
    {
        return $this->app;
    }

    // region: Client resource handling

    public function createResourceCollection() : ClientResourceCollection
    {
        return new ClientResourceCollection($this);
    }

    /**
     * Retrieves the resource manager instance, which is used
     * to keep track of all clientside resources, like JavaScript
     * and stylesheet includes.
     *
     * @return UI_ResourceManager
     */
    public function getResourceManager() : UI_ResourceManager
    {
        return $this->resourceManager;
    }

    public function addStylesheet(string $fileOrUrl, string $media = 'all', int $priority = 0) : UI_ClientResource_Stylesheet
    {
        return $this->resourceManager->addStylesheet($fileOrUrl, $media, $priority);
    }

    /**
     * Adds a JavaScript or stylesheet to include clientside.
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

    /**
     * Adds a JavaScript file to include. This can be either
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

    // endregion
    
   /**
    * Retrieves the build-specific load key that is appended
    * to all JavaScript and stylesheet includes to force a
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
     * Adds a JavaScript statement to run on when the page
     * has loaded using the jquery.ready() function.
     *
     * Example:
     *
     * addJavascriptOnload("alert('Hello World')");
     *
     * @param string $statement
     * @param boolean $avoidDuplicates Whether to ignore identical statements that have already been added
     * @return $this
     */
    public function addJavascriptOnload(string $statement, bool $avoidDuplicates=false) : self
    {
        $statement = rtrim($statement, ';') . ';';
        
        if($avoidDuplicates && in_array($statement, $this->onloadJS, true)) {
            return $this;
        }

        $this->onloadJS[] = $statement;

        return $this;
    }

    /**
     * Adds a JavaScript statement to add to the head script tag.
     * The semicolon is added automatically, so you do not have to
     * include it.
     *
     * Example:
     *
     * addJavascriptHead("alert('Hello World')");
     *
     * @param string $statement
     * @param bool $addSemicolon
     * @return $this
     */
    public function addJavascriptHead(string $statement, bool $addSemicolon=true) : self
    {
        if($addSemicolon) {
        	$statement = rtrim($statement, ';') . ';';
        }

        $this->headJS[] = $statement;

        return $this;
    }

    /**
     * @param string|int|float|StringableInterface|NULL $comment
     * @return $this
     */
    public function addJavascriptHeadComment(string|int|float|StringableInterface|NULL $comment=null) : self
    {
        return $this->addJavascriptHead('// '.$comment, false);
    }

    /**
     * @param string|int|float|StringableInterface|NULL $heading
     * @return $this
     */
    public function addJavascriptHeadHeading(string|int|float|StringableInterface|NULL $heading) : self
    {
        $this->addJavascriptHeadComment(str_repeat('-', 65));
        $this->addJavascriptHeadComment($heading);
        $this->addJavascriptHeadComment(str_repeat('-', 65));

        return $this;
    }

    /**
     * Adds a JavaScript variable to the head script tag. The variable
     * is automatically converted to the JavaScript equivalent.
     *
     * @param string $varName
     * @param mixed $varValue
     * @return $this
     */
    public function addJavascriptHeadVariable(string $varName, mixed $varValue) : self
    {
        $this->headJS[] = JSHelper::buildVariable($varName, $varValue);
        return $this;
    }

    /**
     * Builds and adds a JavaScript statement to the head script tag.
     * The first parameter is the JavaScript function to call, any
     * additional parameters are used as arguments for the JavaScript
     * function call. Variable types are automagically converted to
     * JavaScript types.
     *
     * Examples:
     *
     * // add an alert(); statement:
     * addJavascriptHeadStatement('alert');
     *
     * // add an alert('Alert text'); statement
     * addJavascriptHeadStatement('alert', 'Alert text');
     */
    public function addJavascriptHeadStatement() : self
    {
        $args = func_get_args();
        $statement = call_user_func_array(array('JSHelper', 'buildStatement'), $args);
        $this->headJS[] = $statement;

        return $this;
    }

   /**
    * Like {@link addJavascriptHeadStatement()}, but adds the statement
    * to the onload script block.
    * 
    * @see addJavascriptHeadStatement()
    */
    public function addJavascriptOnloadStatement() : self
    {
        $args = func_get_args();
        $statement = call_user_func_array(array('JSHelper', 'buildStatement'), $args);
        $this->addJavascriptOnload($statement);

        return $this;
    }

    /**
     * Adds a message to be displayed to the user. It is stored in
     * the session, so it will be displayed on the next request if
     * it cannot be shown during the current request (like after
     * saving a record followed by a redirect).
     *
     * @param string|int|float|StringableInterface $message
     * @param string $type
     * @throws UI_Exception
     * @see getMessages()
     * @see clearMessages()
     * @see hasMessages()
     */
    public function addMessage(string|int|float|StringableInterface $message, string $type = UI::MESSAGE_TYPE_INFO) : void
    {
        $messages = $this->getMessages();
        $messages[] = array(
            'type' => $type,
            'text' => toString($message)
        );

        $this->session->setValue(self::SESSION_VAR_APP_MESSAGES, $messages);
    }

    /**
     * @param string|int|float|StringableInterface $message
     * @throws UI_Exception
     */
    public function addSuccessMessage(string|int|float|StringableInterface $message) : void
    {
        $this->addMessage($message, self::MESSAGE_TYPE_SUCCESS);
    }

    /**
     * @param string|int|float|StringableInterface $message
     * @throws UI_Exception
     */
    public function addErrorMessage(string|int|float|StringableInterface $message) : void
    {
        $this->addMessage($message, self::MESSAGE_TYPE_ERROR);
    }

    /**
     * @param string|int|float|StringableInterface $message
     * @throws UI_Exception
     */
    public function addInfoMessage(string|int|float|StringableInterface $message) : void
    {
        $this->addMessage($message);
    }

    /**
     * @param string|int|float|StringableInterface $message
     * @throws UI_Exception
     */
    public function addWarningMessage(string|int|float|StringableInterface $message) : void
    {
        $this->addMessage($message, self::MESSAGE_TYPE_WARNING);
    }

    /**
     * Checks if any user messages are present.
     * @return boolean
     */
    public function hasMessages() : bool
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
     * @return array<int,array{type:string,message:string}>
     * @see addMessage()
     * @see hasMessages()
     * @see clearMessages()
     */
    public function getMessages() : array
    {
        $messages = $this->session->getValue(self::SESSION_VAR_APP_MESSAGES, array());
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
        $this->session->setValue(self::SESSION_VAR_APP_MESSAGES, array());
        
        return $this;
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
            HTML_QuickForm2_Rule_Equals::class
        );
        
        self::$formsInitDone = true;
    }

    /**
     * Sets the current page object; this is done automatically by the
     * application on startup.
     *
     * @param UI_Page $page
     * @throws UI_Exception
     */
    public function setPage(UI_Page $page) : void
    {
        if (isset($this->page))
        {
            throw new UI_Exception(
                'The page may only be set once at startup',
                'Tried to set the page instance for the UI.',
                self::ERROR_CANNOT_SET_PAGE_INSTANCE_AGAIN
            );
        }

        $this->page = $page;
    }
    
    public function hasPage() : bool
    {
        return isset($this->page);
    }

    /**
     * Retrieves the current page object.
     *
     * @throws Exception
     * @return UI_Page
     */
    public function getPage() : UI_Page
    {
        if (!isset($this->page))
        {
            $this->page = new UI_Page($this, 'dummy');
        }

        return $this->page;
    }

    public function renderHeadIncludes() : string
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
    
    protected function resetIncludes() : void
    {
        $this->headJS = array();
        $this->onloadJS = array();
        $this->markupEditorInstances = array();
        $this->resourceManager = new UI_ResourceManager($this);
    }

    // region: Create UI helpers

    /**
     * @param array<string,string|int|float|bool|null> $params
     * @return AdminURLInterface
     */
    public static function adminURL(array $params=array()) : AdminURLInterface
    {
        return AdminURL::create($params);
    }

    public static function createNavigation(string $navigationID) : UI_Page_Navigation
    {
        return self::getInstance()->getPage()->createNavigation($navigationID);
    }

    /**
     * @param string|int|float|StringableInterface|NULL $hint
     * @return SystemHint
     * @throws UI_Exception
     */
    public static function systemHint(string|int|float|StringableInterface|NULL $hint=null) : SystemHint
    {
        return new SystemHint(self::getInstance()->getPage())->setContent($hint);
    }

    /**
     * Creates a popover instance, which can be used to display
     * a toggleable detailed popup. It is the big brother of the
     * tooltip, but less detailed than a dialog.
     *
     * @param string $attachToID The ID of the element to attach to.
     * @return UI_Bootstrap_Popover
     * @throws UI_Exception
     */
    public static function popover(string $attachToID) : UI_Bootstrap_Popover
    {
        return new UI_Bootstrap_Popover(self::getInstance())->setAttachToID($attachToID);
    }

    /**
     * Creates a tooltip instance used to hold information
     * for a tooltip, to allow configuring it further.
     *
     * Usage:
     *
     * 1) Set the element ID to attach it to.
     *    Either use {@see TooltipInfo::attachToID()}, or
     *    {@see TooltipInfo::injectAttributes()} to use an
     *    existing `id` attribute (or create one automatically).
     * 2) Enable the tooltip. It will be automatically enabled
     *    if it is rendered to string, if {@see TooltipInfo::injectAttributes()}
     *    is called, or if {@see TooltipInfo::injectJS()} is called.
     *
     * @param string|int|float|StringableInterface|TooltipInfo|NULL $content
     * @return TooltipInfo
     * @throws UI_Exception
     */
    public static function tooltip(string|int|float|StringableInterface|TooltipInfo|NULL $content) : TooltipInfo
    {
        return TooltipInfo::create($content);
    }

    /**
     * Creates a new Badge UI element and returns it. These can
     * be converted to string, so they can be inserted directly
     * into any content strings.
     *
     * @param string|int|float|StringableInterface $label
     * @return UI_Badge
     * @throws UI_Exception
     */
    public static function badge(string|int|float|StringableInterface $label) : UI_Badge
    {
        return new UI_Badge($label);
    }

    /**
     * Creates a new Label UI element and returns it. These can
     * be converted to string, so they can be inserted directly
     * into any content strings.
     *
     * @param string|int|float|StringableInterface $label
     * @return UI_Label
     * @throws UI_Exception
     */
    public static function label(string|int|float|StringableInterface $label) : UI_Label
    {
        return new UI_Label($label);
    }

    public function createPagination(PaginationHelper $helper, string $pageParamName, string $baseURL) : PaginationRenderer
    {
        return new PaginationRenderer($helper, $pageParamName, $baseURL);
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
     * @param string $id
     * @return UI_QuickSelector
     */
    public function createQuickSelector(string $id='') : UI_QuickSelector
    {
        return new UI_QuickSelector($id);
    }

    /**
     * Creates a new UI message instance and returns it.
     *
     * @param string|int|float|StringableInterface|NULL $message
     * @param string $type
     * @param array<string,mixed> $options
     * @return UI_Message
     */
    public function createMessage(string|int|float|StringableInterface|NULL $message=null, string $type=UI::MESSAGE_TYPE_INFO, array $options=array()) : UI_Message
    {
        return new UI_Message($this, $message, $type, $options);
    }

    /**
     * Creates a new template instance for the specified template ID or class name.
     *
     * @param string|class-string<UI_Page_Template> $templateIDOrClass
     * @return UI_Page_Template
     */
    public function createTemplate(string $templateIDOrClass) : UI_Page_Template
    {
        return $this->getPage()->createTemplate($templateIDOrClass);
    }

    public static function string() : UI_StringBuilder
    {
        return new UI_StringBuilder();
    }

    /**
     * Creates a new form object used as wrapper around the HTML_QuickForm2
     * object to make handling forms easier within the application.
     *
     * @param string $id
     * @param array<string,mixed>|ArrayDataCollection $defaultData
     * @return UI_Form
     * @see createGetForm()
     */
    public function createForm(string $id, array|ArrayDataCollection $defaultData = array()) : UI_Form
    {
        self::initForms();

        $form = new UI_Form($this, $id, 'post', $defaultData);

        if(Application_EventHandler::hasListener('FormCreated'))
        {
            Application_EventHandler::trigger('FormCreated', array($form), UI_Event_FormCreated::class);
        }

        return $form;
    }

    /**
     * Creates a new form object that gets submitted via get instead
     * of the default post method.
     *
     * @param string $id
     * @param array<string,mixed> $defaultData
     * @return UI_Form
     */
    public function createGetForm(string $id, array $defaultData = array()) : UI_Form
    {
        self::initForms();

        return new UI_Form($this, $id, 'get', $defaultData);
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
    public function createDataGrid(string $id, bool $allowDuplicateID=false) : UI_DataGrid
    {
        return new UI_DataGrid($this, $id, $allowDuplicateID);
    }

    /**
     * @param string $type
     * @return UI_Interfaces_Bootstrap
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function createBootstrap(string $type) : UI_Interfaces_Bootstrap
    {
        $class = ClassHelper::requireResolvedClass('UI_Bootstrap_'.$type);

        return ClassHelper::requireObjectInstanceOf(
            UI_Interfaces_Bootstrap::class,
            new $class($this)
        );
    }

    /**
     * @param string|int|float|StringableInterface|NULL $label
     * @return UI_Bootstrap_ButtonDropdown
     */
    public function createButtonDropdown(string|int|float|StringableInterface|NULL $label=null) : UI_Bootstrap_ButtonDropdown
    {
        $dropDown = new UI_Bootstrap_ButtonDropdown($this);
        $dropDown->setLabel($label);

        return $dropDown;
    }
    
   /**
    * @param string $label
    * @return UI_Bootstrap_BadgeDropdown
    */
    public function createBadgeDropdown(string $label='') : UI_Bootstrap_BadgeDropdown
    {
        $dropdown = new UI_Bootstrap_BadgeDropdown($this);
        $dropdown->setLabel($label);
        
        return $dropdown;
    }
    
   /**
    * @param string $label
    * @param string|AdminURLInterface $url
    * @return UI_Bootstrap_Anchor
    */
    public function createAnchor(string $label='', string|AdminURLInterface $url='') : UI_Bootstrap_Anchor
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

    public function createTreeRenderer(TreeNode $rootNode) : TreeRenderer
    {
        return new TreeRenderer($this, $rootNode);
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

    /**
     * @param string|int|float|StringableInterface|NULL $label
     * @return UI_Bootstrap_DropdownAnchor
     * @throws UI_Exception
     */
    public function createDropdownAnchor(string|int|float|StringableInterface|NULL $label) : UI_Bootstrap_DropdownAnchor
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
     * @param string|int|float|StringableInterface|NULL $content
     * @return UI_Bootstrap_DropdownStatic
     * @throws UI_Exception
     */
    public function createDropdownStatic(string|int|float|StringableInterface|NULL $content) : UI_Bootstrap_DropdownStatic
    {
        return new UI_Bootstrap_DropdownStatic($this)
            ->setContent($content);
    }

    /**
     * @param string|int|float|StringableInterface|NULL $title
     * @return UI_Bootstrap_DropdownSubmenu
     * @throws UI_Exception
     */
    public function createDropdownSubmenu(string|int|float|StringableInterface|NULL $title=null) : UI_Bootstrap_DropdownSubmenu
    {
        return new UI_Bootstrap_DropdownSubmenu($this)
            ->setTitle($title);
    }

    /**
     * Creates and returns a new UI icon object.
     * @return UI_Icon
     */
    public static function icon() : UI_Icon
    {
        return new UI_Icon();
    }

    /**
     * @param string|bool|int $boolValue
     * @return UI_PrettyBool
     * @throws ConvertHelper_Exception
     */
    public static function prettyBool(string|bool|int $boolValue) : UI_PrettyBool
    {
        return new UI_PrettyBool($boolValue);
    }
    
   /**
    * Creates and returns a new UI button object.
    * Use the button's API to configure its looks 
    * and functions. It supports string conversion.
    * 
    * @param string|int|float|StringableInterface|NULL $label
    * @return UI_Button
    */
    public static function button(string|int|float|StringableInterface|NULL $label=null) : UI_Button
    {
    	return new UI_Button($label);
    }

    /**
     * @param string|int|float|StringableInterface|NULL $label
     * @return UI_Bootstrap_ButtonDropdown
     * @throws UI_Exception
     */
    public static function buttonDropdown(string|int|float|StringableInterface|NULL $label=null) : UI_Bootstrap_ButtonDropdown
    {
        return self::getInstance()->createButtonDropdown($label);
    }

    public function createPropertiesGrid(string $id='') : UI_PropertiesGrid
    {
        return new UI_PropertiesGrid($this->getPage(), $id);
    }

    // endregion

    /**
     * Adds output to the console output, which is displayed
     * for developer users.
     *
     * @since 3.3.5
     * @param string $markup
     */
    public function addConsoleOutput(string $markup) : void
    {
        $this->getPage()->addConsoleOutput($markup);
    }

    /**
     * Sets the key to add to all scripts to make sure they are
     * refreshed on the client when the key changes. Note that this
     * has to set before any scripts are added to make sure it
     * is used everywhere.
     *
     * @param string $key
     */
    public function setIncludesLoadKey(string $key) : void
    {
        $this->loadKey = $key;
    }

    /**
     * Adds clientside support for adding progress bars to the page
     * using the ProgressBar class.
     */
    public function addProgressBar() : void
    {
        $this->addJavascript('ui/progressbar.js');
        $this->addJavascript('dialog/progressbar.js');
        $this->addStylesheet('ui-progressbar.css');
    }
    
    public function addBootstrap() : self
    {
        $this->addStylesheet('bootstrap.min.css', 'all', 8000);
        $this->addJavascript('bootstrap.min.js', 8800);
        return $this;
    }
    
    public const string FONT_AWESOME_URL = 'https://use.fontawesome.com/releases/v5.15.4/css/all.css';
    
    public function addFontAwesome() : self
    {
        $this->addStylesheet(self::FONT_AWESOME_URL);
        
        // the JS SVG version does not work yet: JS handlers on the icons
        // do not work. See here for information and possible fixes:
        // https://fontawesome.com/how-to-use/svg-with-js#with-jquery
        //$this->addJavascript('https://use.fontawesome.com/releases/v5.0.8/js/all.js', null, true);

        return $this;
    }
    
    public function addJqueryUI() : self
    {
        $this->addStylesheet('jquery-ui.min.css', 'screen', 9000);
        $this->addJavascript('jquery-ui.min.js', 8900);
        $this->addJavascript('jquery-ui-timepicker.js', 8600);

        return $this;
    }
    
    public function addJquery() : self
    {
        $prio = 9000;

        $this->addJavascript('jquery.min.js', $prio);
        $this->addJavascript('jquery-custom-extensions.js', $prio-1);

        return $this;
    }
    
    /**
     * Adds a redactor UI element.
     *
     * @param HTML_QuickForm2_Element $element
     * @param Application_Countries_Country $country
     * @return UI_MarkupEditor_Redactor
     *
     * @throws UnexpectedInstanceException
     */
    public function addRedactor(HTML_QuickForm2_Element $element, Application_Countries_Country $country) : UI_MarkupEditor_Redactor
    {
        $editor = $this->addMarkupEditor('Redactor', $element, $country);

        if($editor instanceof UI_MarkupEditor_Redactor)
        {
            return $editor;
        }

        throw new UnexpectedInstanceException(UI_MarkupEditor_Redactor::class, $editor);
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
        $class = UI_MarkupEditor::class.'_'.$id;
        
        $redactor = new $class($this, $element, $country);
        
        $this->markupEditorInstances[] = $redactor;

        return $redactor;
    }

    public static function printBacktrace() : void
    {
        $trace = debug_backtrace();
        array_shift($trace); // remove own call
        $trace = array_reverse($trace);
        
        OutputBuffering::start();

        ?>
        <style>
            .backtrace{
                padding:8px;
                background:#fff;
                margin:13px;
                border:solid 1px #ccc;
                border-radius:4px;
            }

            .backtrace TD,
            .backtrace TH{
                padding:3px 6px;
                color:#454545;
                font-family:monospace;
            }
        </style>
        <div class="backtrace">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:right">File</th>
                        <th style="text-align:right">Line</th>
                        <th style="text-align:left">Call</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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
                                    $args[] = '"'.htmlspecialchars(trim($arg), ENT_QUOTES).'"';
                                    break;
                            }
                        }
                        
                        $call .= '('.implode(', ', $args).')';
                        
                        ?>
                        <tr>
                            <td style="text-align:right">
                                <?php echo FileHelper::relativizePath($entry['file'], APP_ROOT); ?>
                            </td>
                            <td style="text-align:right">
                                <?php echo $entry['line'] ?>
                            </td>
                            <td>
                                <?php echo $call ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
                
        echo OutputBuffering::get();
    }
    
   /**
    * Adds support for clientside data grid building with
    * the `UI_DataGrid` classes.
    * 
    * @return UI
    */    
    public function addDataGridSupport() : UI
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
    * Requires the subject to be a scalar value, or an object instance of the renderable interface.
    * 
    * @param mixed|StringableInterface $subject
    * @throws UI_Exception
    * @return string|int|float|bool|StringableInterface
    * 
    * @see UI::ERROR_NOT_A_RENDERABLE
    */
    public static function requireRenderable(mixed $subject) : string|int|float|bool|StringableInterface
    {
        if(is_scalar($subject) || $subject instanceof StringableInterface) {
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
        $name = Application_Driver::createSettings()->get(UI_MarkupEditorInfo::SETTING_NAME_MARKUP_EDITOR_ID);

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
        return boot_constant(BaseConfigRegistry::JAVASCRIPT_MINIFIED) === true;
    }

    /**
     * Adds a listener for the page rendered event, which
     * is called once the whole page to be sent to the browser
     * has been rendered. It allows modifying the HTML code
     * before it is sent to the browser.
     *
     * @param callable $listener
     * @return Application_EventHandler_Listener
     */
    public static function onPageRendered(callable $listener) : Application_EventHandler_Listener
    {
        return Application_EventHandler::addListener(
            self::EVENT_PAGE_RENDERED,
            $listener
        );
    }
}
