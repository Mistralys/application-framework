<?php
/**
 * File containing the {@see UI_Themes_Theme_ContentRenderer} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Themes_Theme_ContentRenderer
 */

declare(strict_types=1);

use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

/**
 * A content renderer is automatically given to each UI_Page instance,
 * and is used to customize the upper scaffold of the pages, like the
 * page title, abstract, etc., as well as to hold the content that is
 * shown in the page.
 * 
 * NOTE: implements the Renderable interface, but does not extend the
 * Renderable class because of timing issues.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Themes_Theme_ContentRenderer implements OptionableInterface, UI_Renderable_Interface
{
    use OptionableTrait;
    use UI_Traits_RenderableGeneric;
    
   /**
    * @var array<string,string>
    */
    protected array $templates = array(
        'false' => 'frame.content.without-sidebar',
        'true' => 'frame.content.with-sidebar'
    );
    
   /**
    * @var array<string,mixed>
    */
    protected array $templateVars = array();
    protected string $content = '';
    protected UI $ui;
    protected ?UI_Page_Title $title = null;
    protected ?UI_Page_Subtitle $subtitle = null;
    
    public function getDefaultOptions() : array
    {
        return array(
            'sidebar' => false,
            'abstract' => '',
        );
    }
    
    public function __construct(UI $ui)
    {
        $this->ui = $ui;
    }
    
    public function getUI() : UI
    {
        return $this->ui;
    }
    
    public function getRenderer() : UI_Themes_Theme_ContentRenderer
    {
        return $this;
    }
    
   /**
    * Enables the sidebar (off by default).
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function makeWithSidebar() : UI_Themes_Theme_ContentRenderer
    {
        return $this->setWithSidebar(true);
    }

   /**
    * Disable the sidebar (off by default).
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function makeWithoutSidebar() : UI_Themes_Theme_ContentRenderer
    {
        return $this->setWithSidebar(false);
    }
    
    public function setWithSidebar(bool $with=true) : UI_Themes_Theme_ContentRenderer
    {
        return $this->setOption('sidebar', $with);
    }
    
   /**
    * Sets the page title. This is used as the browser title
    * as well if the page has not been given a specific title.
    * 
    * @param string|number|UI_Renderable_Interface $title
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function setTitle($title) : UI_Themes_Theme_ContentRenderer
    {
        $this->getTitle()->setText($title);
        
        return $this;
    }
    
   /**
    * Sets the subline text to show directly beneath the title.
    * 
    * @param string|number|UI_Renderable_Interface $subline
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function setTitleSubline($subline) : UI_Themes_Theme_ContentRenderer
    {
        $this->getTitle()->setSubline($subline);
        
        return $this;
    }
    
   /**
    * Sets the page's abstract text, shown below the subnavigation.
    * 
    * @param string|number|UI_Renderable_Interface $abstract
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function setAbstract($abstract) : UI_Themes_Theme_ContentRenderer
    {
        return $this->setOption('abstract', toString($abstract));
    }
    
   /**
    * Sets a subtitle for the page, shown above the abstract and the subnavigation.
    * 
    * @param string|number|UI_Renderable_Interface $subtitle
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function setSubtitle($subtitle) : UI_Themes_Theme_ContentRenderer
    {
        $this->getSubtitle()->setText($subtitle);
        
        return $this;
    }
    
   /**
    * @param string|number|UI_Renderable_Interface $content
    * @return $this
    */
    public function setContent($content) : self
    {
        $this->content = toString($content);
        return $this;        
    }
    
   /**
    * @param string|number|UI_Renderable_Interface $content
    * @return $this
    */
    public function appendContent($content) : self
    {
        $this->content .= toString($content);
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setTemplateVar(string $name, $value) : self
    {
        $this->templateVars[$name] = $value;
        return $this;
    }

    /**
     * @param Application_Interfaces_Formable $formable
     * @return $this
     */
    public function appendFormable(Application_Interfaces_Formable $formable) : self
    {
        return $this->appendForm($formable->getFormInstance());
    }

    /**
     * @param UI_Form $form
     * @return $this
     * @throws UI_Themes_Exception
     * @throws BaseClassHelperException
     */
    public function appendForm(UI_Form $form) : self
    {
        $html = $this->getPage()->renderTemplate(
            'frame.content.form',
            array(
                'form-object' => $form,
            )
        );
        
        return $this->appendContent($html);
    }

    /**
     * @param UI_DataGrid $grid
     * @param array<int,array<string,mixed>|UI_DataGrid_Entry> $entries
     * @return $this
     *
     * @throws Application_Exception
     * @throws UI_Themes_Exception
     * @throws BaseClassHelperException
     */
    public function appendDataGrid(UI_DataGrid $grid, array $entries) : self
    {
        $html = $this->getPage()->renderTemplate(
            'frame.content.datagrid',
            array(
                'datagrid-object' => $grid,
                'datagrid-html' => $grid->render($entries),
            )
        );
        
        return $this->appendContent($html);
    }

    /**
     * @param string $templateIDOrClass
     * @param array<string,mixed> $vars
     * @return $this
     *
     * @throws UI_Themes_Exception
     * @throws BaseClassHelperException
     */
    public function appendTemplateClass(string $templateIDOrClass, array $vars=array()) : self
    {
        return $this->appendTemplate(
            $this->getPage()
                ->createTemplate($templateIDOrClass)
                ->setVars($vars)
        );
    }

    /**
     * @param UI_Page_Template $template
     * @return $this
     */
    public function appendTemplate(UI_Page_Template $template) : self
    {
        return $this->appendContent($template->render());
    }
    
    public function render() : string
    {
        $enabled = ConvertHelper::boolStrict2string($this->isWithSidebar());

        return $this->getPage()->renderTemplate(
            $this->templates[$enabled],
            array(
                'renderer' => $this
            )
        );
    }
    
    public function getAbstract() : string
    {
        return $this->getStringOption('abstract');
    }
    
    public function getTitle() : UI_Page_Title
    {
        if(!isset($this->title)) {
            $this->title = new UI_Page_Title($this->ui->getPage());
        }
        
        return $this->title;
    }
    
    public function isWithSidebar() : bool
    {
        return $this->getBoolOption('sidebar');
    }
    
    public function getSubtitle() : UI_Page_Subtitle
    {
        if(!isset($this->subtitle)) {
            $this->subtitle = new UI_Page_Subtitle($this->ui->getPage());
        }
        
        return $this->subtitle;
    }
    
    public function getContent() : string
    {
        return $this->content;
    }
    
    public function hasTitle() : bool
    {
        return isset($this->title) && $this->title->isValid();
    }
    
    public function hasSubtitle() : bool
    {
        return isset($this->subtitle) && $this->subtitle->isValid();
    }
    
    public function hasAbstract() : bool
    {
        return !$this->isOption('abstract', '');
    }
}
