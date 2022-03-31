<?php
/**
 * File containing the {@see UI_Page_Template} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Template
 */

use AppUtils\FileHelper;
use UI\Interfaces\PageTemplateInterface;
use function AppUtils\parseVariable;

/**
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Template extends UI_Renderable implements PageTemplateInterface
{
    public const ERROR_TEMPLATE_FILE_NOT_FOUND = 27301;
    public const ERROR_NOT_EXPECTED_OBJECT_INSTANCE = 27302;
    
    /**
     * @var UI_Page_Sidebar
     */
    protected $sidebar;

    /**
     * @var UI_Page_Header
     */
    protected $header;

    /**
     * @var UI_Page_Footer
     */
    protected $footer;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Application_Request
     */
    protected $request;

   /**
    * @var string
    */
    protected $templateID;

   /**
    * @var string
    */
    protected $templateFile;

    protected $options = array();

    /**
     * @var Application_User
     */
    protected $user;

    public function __construct(UI_Page $page, $templateID)
    {
        parent::__construct($page);
        
        $this->application = $this->ui->getApplication();
        $this->request = $this->application->getRequest();
        $this->user = $this->driver->getUser();
        $this->sidebar = $this->page->getSidebar();
        $this->header = $this->page->getHeader();
        $this->footer = $this->page->getFooter();

        if(stripos($templateID, '.php') !== false) {
            $templateID = FileHelper::removeExtension($templateID, true);
        }

        $this->templateID = $templateID;
        $this->templateFile = $this->theme->getTemplatePath($templateID.'.php');

        if (!file_exists($this->templateFile)) {
            throw new UI_Themes_Exception(
                sprintf(
                    'Template %s does not exist',
                    $templateID
                ),
                sprintf(
                    'The template %s does not exist in theme %s. Should be stored in %s.',
                    $templateID,
                    $this->theme->getID(),
                    $this->templateFile
                ),
                self::ERROR_TEMPLATE_FILE_NOT_FOUND
            );
        }
    }

    protected function _render()
    {
        ob_start();
        include $this->templateFile;
        return ob_get_clean();
    }

    public function setVars(array $vars) : UI_Page_Template
    {
        foreach ($vars as $var => $value) {
            $this->setVar($var, $value);
        }

        return $this;
    }

    /**
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderSuccessMessage(string $message, array $options = array()) : string
    {
        return $this->page->renderSuccessMessage($message, $options);
    }

    /**
     * @param string $message
     * @param string $type
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderMessage(string $message, string $type, array $options = array()) : string
    {
        return $this->page->renderMessage($message, $type, $options);
    }

    /**
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderInfoMessage(string $message, array $options = array()) : string
    {
        return $this->page->renderInfoMessage($message, $options);
    }

    /**
     * @param string $message
     * @param array<string,mixed> $options
     * @return string
     */
    public function renderErrorMessage(string $message, array $options = array()) : string
    {
        return $this->page->renderErrorMessage($message, $options);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setVar(string $name, $value) : self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed|NULL $default
     * @return mixed|NULL
     */
    public function getVar(string $name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * @param string $name
     * @param string $className
     * @return object
     *
     * @throws UI_Themes_Exception
     * @see UI_Page_Template::ERROR_INVALID_TEMPLATE_CLASS
     */
    public function getObjectVar(string $name, string $className) : object
    {
        $result = $this->getVar($name);
        
        if(is_a($result, $className))
        {
            return $result;
        }
        
        throw new UI_Themes_Exception(
            'Invalid object instance in template variable.',
            sprintf(
                'Expected [%s], given [%s].',
                $className,
                parseVariable($result)->enableType()->toString()
            ),
            self::ERROR_NOT_EXPECTED_OBJECT_INSTANCE
        );
    }
    
    public function getBoolVar(string $name) : bool
    {
        return $this->getVar($name) === true;
    }
    
    public function getArrayVar(string $name) : array
    {
        $var = $this->getVar($name);
        
        if(is_array($var))
        {
            return $var;
        }
        
        return array();
    }
    
    public function getStringVar(string $name) : string
    {
        return (string)$this->getVar($name);
    }

    /**
     * @param string $name
     * @param mixed|NULL $default
     * @return $this
     */
    public function printVar(string $name, $default = null) : self
    {
        echo $this->getVar($name, $default);
        return $this;
    }

    public function getLogoutURL() : string
    {
        return $this->buildURL(array('logout' => 'yes'));
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function buildURL(array $params) : string
    {
        return $this->request->buildURL($params);
    }

    public function getImageURL(string $imageName) : string
    {
        return $this->theme->getImageURL($imageName);
    }

    /**
     * @param string $templateID
     * @param array<string,mixed> $params
     * @return $this
     * @throws Application_Exception
     */
    public function printTemplate(string $templateID, array $params = array()) : self
    {
        echo $this->renderTemplate($templateID, $params);
        return $this;
    }

    public function hasVar(string $name) : bool
    {
        return isset($this->options[$name]);
    }

    public function hasVarNonEmpty(string $name) : bool
    {
        return isset($this->options[$name]) && !empty($this->options[$name]);
    }

    /**
     * Renders the content template with sidebar.
     *
     * @param string $content
     * @param string $title
     * @param string $titleRight HTML content to float on the right of the title
     * @return string
     */
    public function renderContentWithSidebar(string $content, string $title = '', string $titleRight = '')
    {
        return $this->renderTemplate(
            'frame.content.with-sidebar',
            array(
                'title' => $title,
                'title-right' => $titleRight,
                'content' => $content
            )
        );
    }

    /**
     * Renders the content template with sidebar and echos it.
     *
     * @param string $content
     * @param string $title
     * @param string $titleRight
     * @return $this
     *
     * @throws Application_Exception
     */
    public function printContentWithSidebar(string $content, string $title = '', string $titleRight = '') : self
    {
        echo $this->renderContentWithSidebar($content, $title, $titleRight);
        return $this;
    }

    /**
     * Renders the content template without sidebar.
     *
     * @param string $content
     * @param string $title
     */
    public function renderContentWithoutSidebar($content, $title = null, $titleRight = null)
    {
        return $this->renderTemplate(
            'frame.content.without-sidebar',
            array(
                'title' => $title,
                'title-right' => $titleRight,
                'content' => $content
            )
        );
    }

    /**
     * Renders the content template without sidebar and echos it.
     *
     * @param string $content
     * @param string $title
     */
    public function printContentWithoutSidebar($content, $title = null, $titleRight = null)
    {
        echo $this->renderContentWithoutSidebar($content, $title, $titleRight);
    }

    /**
     * Renders a content section with the specified content and
     * optional title. For more configuration options, consider
     * using the {@link createSection} method to work with a 
     * section helper class instance directly.
     *
     * @param string $content
     * @param string $title
     * @param string $abstract
     * @return string
     * @see printSection()
     * @see createSection()
     */
    public function renderSection($content, $title = null, $abstract=null)
    {
        return $this->createSection()
            ->setTitle($title)
            ->setContent($content)
            ->setAbstract($abstract)
            ->render();
    }

    /**
     * Like {@link renderSection()} but echos the generated content.
     *
     * @param string $content
     * @param string $title
     * @param string $abstract
     * @see renderSection()
     */
    public function printSection($content, $title = null, $abstract=null)
    {
        echo $this->renderSection($content, $title, $abstract);
    }

   /**
    * Creates a new page section object that can be used to
    * configure a section further than the renderSection 
    * method allows.
    * 
    * @return UI_Page_Section
    */
    public function createSection() : UI_Page_Section
    {
        return $this->page->createSection();
    }
    
   /**
    * Renders an empty body with custom markup.
    * @param string $html
    * @return string
    */
    public function renderCleanFrame($html)
    {
        return $this->renderTemplate(
            'clean-frame',
            array(
                'content' => $html
            )
        );
    }
    
    public function getAppNameShort() : string
    {
        return $this->driver->getAppNameShort();
    }
    
    public function getAppName() : string
    {
        return $this->driver->getAppName();
    }
}
