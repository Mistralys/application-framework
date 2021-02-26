<?php
/**
 * File containing the {@see UI_Page_Template} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Template
 */

use function AppUtils\parseVariable;

/**
 * Template class: this class is instantiated for each
 * template file, and is the context of the template 
 * in $this.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_Template extends UI_Renderable
{
    const ERROR_TEMPLATE_FILE_NOT_FOUND = 27301;
    const ERROR_NOT_EXPECTED_OBJECT_INSTANCE = 27302;
    
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

   /**
    * @var UI_Themes_Theme_ContentRenderer
    */
    protected $renderer;
    
    public function __construct(UI_Page $page, $templateID)
    {
        parent::__construct($page);
        
        $this->application = $this->ui->getApplication();
        $this->driver = $this->application->getDriver();
        $this->request = $this->application->getRequest();
        $this->user = $this->driver->getUser();
        $this->sidebar = $this->page->getSidebar();
        $this->header = $this->page->getHeader();
        $this->footer = $this->page->getFooter();
        $this->renderer = $this->page->getRenderer();

        if(stristr($templateID, '.php')) {
            $templateID = \AppUtils\FileHelper::removeExtension($templateID, true);
        }

        $this->templateID = $templateID;
        $this->templateFile = $this->theme->getTemplatePath($templateID.'.php');

        if (!file_exists($this->templateFile)) {
            throw new Application_Exception(
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

    /**
     * @param array<string,mixed> $vars
     * @return $this
     */
    public function setVars(array $vars) : UI_Page_Template
    {
        foreach ($vars as $var => $value) {
            $this->setVar($var, $value);
        }

        return $this;
    }

    public function renderSuccessMessage($message, $options = array())
    {
        return $this->page->renderSuccessMessage($message, $options);
    }

    public function renderMessage($message, $type, $options = array())
    {
        return $this->page->renderMessage($message, $type, $options);
    }

    public function renderInfoMessage($message, $options = array())
    {
        return $this->page->renderInfoMessage($message, $options);
    }

    public function renderErrorMessage($message, $options = array())
    {
        return $this->page->renderErrorMessage($message, $options);
    }

    public function setVar(string $name, $value) : UI_Page_Template
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function getVar(string $name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }
    
   /**
    * Retrieves the variable, and ensures that is is an instance
    * of the specified class.
    * 
    * @param string $name
    * @param string $className
    * @return object
    */
    public function getObjectVar(string $name, string $className) : object
    {
        $result = $this->getVar($name);
        
        if(is_a($result, $className))
        {
            return $result;
        }
        
        throw new Application_Exception(
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
    
    public function printVar($name, $default = null)
    {
        echo $this->getVar($name, $default);
    }

    public function getLogoutURL()
    {
        return $this->buildURL(array('logout' => 'yes'));
    }

    public function buildURL($params)
    {
        return $this->request->buildURL($params);
    }

    public function getImageURL($imageName)
    {
        return $this->theme->getImageURL($imageName);
    }

    public function printTemplate($templateID, $params = array())
    {
        echo $this->renderTemplate($templateID, $params);
    }

    /**
     * Checks if the specified variable has been set.
     *
     * @since 3.3.7
     * @return boolean
     */
    public function hasVar($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * Renders the content template with sidebar.
     *
     * @param string $content
     * @param string $title
     * @param string $titleRight HTML content to float on the right of the title
     * @return string
     */
    public function renderContentWithSidebar($content, $title = null, $titleRight = null)
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
     */
    public function printContentWithSidebar($content, $title = null, $titleRight = null)
    {
        echo $this->renderContentWithSidebar($content, $title, $titleRight);
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
    public function createSection()
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
    
    public function getAppNameShort()
    {
        return $this->driver->getAppNameShort();
    }
    
    public function getAppName()
    {
        return $this->driver->getAppName();
    }
}
