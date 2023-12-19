<?php
/**
 * File containing the {@link UI_Renderable}.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Renderable
 * @see UI_Renderable_Interface
 */

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Base class for elements that can be rendered to HTML.
 * Made to be extended, and offer some utility methods
 * on top of the base interface implementation.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Renderable_Interface
 */
abstract class UI_Renderable implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;
    
    public const ERROR_INVALID_TEMPLATE_CLASS = 62301;
    
    protected UI $ui;
    protected Application_Driver $driver;
    protected UI_Themes_Theme $theme;
    protected UI_Page $page;
    protected string $instanceID;
    protected UI_Themes_Theme_ContentRenderer $renderer;

   /**
    * The unique key of the UI instance.
    * @var int
    */
    protected int $uiKey;

    public function __construct(?UI_Page $page=null)
    {
        $this->instanceID = nextJSID();
        $this->driver = Application_Driver::getInstance();
        
        if($page === null) {
            $page = UI::getInstance()->getPage();
        }
        
        $this->renderer = $page->getRenderer();
        $this->ui = $page->getUI();
        $this->uiKey = $this->ui->getInstanceKey();
        $this->page = $page;
        $this->theme = $this->ui->getTheme();
        
        $this->initRenderable();
    }

    /**
     * Creates a new template object for the specified template.
     * Templates are stored in the `templates` sub-folder, specify
     * the name here (without the extension).
     *
     * Example:
     *
     * <pre>
     * // loads templates/content.my-template.php
     * createTemplate('content.my-template');
     * </pre>
     *
     * @param string $templateIDOrClass
     * @return UI_Page_Template
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws UI_Themes_Exception
     * @throws FileHelper_Exception
     */
    public function createTemplate(string $templateIDOrClass) : UI_Page_Template
    {
        if(class_exists($templateIDOrClass)) {
            $className = $templateIDOrClass;
        } else {
            $className = $this->resolveTemplateClass($templateIDOrClass);
        }

        if(class_exists($className))
        {
            return ClassHelper::requireObjectInstanceOf(
                UI_Page_Template_Custom::class,
                new $className($this->page, $templateIDOrClass)
            );
        }
        
        return new UI_Page_Template($this->page, $templateIDOrClass);
    }

    private function resolveTemplateClass(string $templateID) : string
    {
        if(stripos($templateID, '.php') !== false) {
            $templateID = FileHelper::removeExtension($templateID, true);
        }

        $templateFile = $this->theme->getTemplatePath($templateID.'.php');

        $className = '';
        if(strpos($templateFile, $this->theme->getDriverPath()) !== false)
        {
            $className = 'driver_';
        }

        $className .= 'template_default_'.str_replace(array('.', '/', '-'), '_', $templateID);

        return $className;
    }

    /**
     * Creates a template, renders it and returns the generated contents.
     *
     * @param string $templateID
     * @param array<string,mixed> $params
     * @return string
     * @throws UI_Themes_Exception
     * @see UI_Renderable::createTemplate()
     */
    public function renderTemplate(string $templateID, array $params = array()) : string
    {
        $tpl = $this->createTemplate($templateID);
        $tpl->setVars($params);
        
        return $tpl->render();
    }

    /**
     * @param string $templateID
     * @param array<string,mixed> $params
     * @throws UI_Themes_Exception
     */
    public function displayTemplate(string $templateID, array $params=array()) : void
    {
        echo $this->renderTemplate($templateID, $params);
    }
    
   /**
    * Creates a new UI message instance and returns it.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param string $type
    * @param array<string,mixed> $options
    * @return UI_Message
    */
    public function createMessage($message, string $type=UI::MESSAGE_TYPE_INFO, array $options=array()) : UI_Message
    {
        return $this->ui->createMessage($message, $type, $options);
    }
    
    protected function initRenderable() : void
    {
        
    }
    
    public function getRenderer() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer;
    }
    
    public function getPage() : UI_Page
    {
        return $this->page;
    }
    
    public function getUI() : UI
    {
        return $this->ui;
    }
    
    public function getTheme() : UI_Themes_Theme
    {
        return $this->theme;
    }
    
    public function getInstanceID() : string
    {
        return $this->instanceID;
    }
    
    public function render() : string
    {
        return $this->_render();
    }
    
   /**
    * @return string
    */
    abstract protected function _render();
    
    public function display() : void
    {
        echo $this->render();
    }
    
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch(Exception $e) 
        {
            return $this->ui->getPage()->renderErrorMessage(
                t('Cannot render element %1$s:', get_class($this)).' '.
                t('The exception %1$s occurred with message %2$s.', $e->getCode(), $e->getMessage())    
            );
        }
    }
}