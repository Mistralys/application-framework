<?php
/**
 * File containing the {@link UI_Form_Renderer_Element} class.
 * 
 * @package Forms
 * @subpackage Renderer
 * @see UI_Form_Renderer_Element
 */

declare(strict_types=1);

/**
 * Renders the markup for form elements, and provides 
 * utility methods to customize this markup. This class
 * is provided as argument to the element's render callback
 * functions.
 * 
 * @package Forms
 * @subpackage Renderer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see UI_Form::addRenderCallback()
 * @see UI_Form_Renderer_RenderType_Default
 */
class UI_Form_Renderer_Element extends UI_Renderable
{
    const HTML_BELOW_COMMENT = 'below_comment';
    const HTML_ABOVE_CONTROL = 'above_control';
    const HTML_BELOW_CONTROL = 'below_control';
    
    protected $injectHTML = array();
    
   /**
    * @var UI_Icon[]
    */
    protected $icons = array();
    
   /**
    * @var UI_Form_Renderer
    */
    protected $formRenderer;
    
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef
    */
    private $renderDef;

   /**
    * @var UI_Form_Renderer_RenderType_Default
    */
    private $renderType;
    private ?string $elementHTML = null;

    /**
    * @param UI_Form_Renderer_RenderType_Default $renderType
    */
    public function __construct(UI_Form_Renderer_RenderType_Default $renderType)
    {
        $this->formRenderer = $renderType->getRenderer();
        $this->renderType = $renderType;
        $this->renderDef = $renderType->getRenderDef();
        
        parent::__construct($this->formRenderer->getPage());
        
        // convert the prepend-html and append-html features
        // to the way we handle things internally
        $this->initInjectPosition('prepend', self::HTML_ABOVE_CONTROL);
        $this->initInjectPosition('append', self::HTML_BELOW_CONTROL);
        
        $this->initIcons();
    }
    
   /**
    * Retrieves the form element being rendered.
    * @return HTML_QuickForm2_Node
    */
    public function getFormElement() : HTML_QuickForm2_Node
    {
        return $this->renderDef->getElement();
    }
    
    public function isFrozen() : bool
    {
        return $this->renderDef->isFrozen();
    }
    
    public function isStandalone() : bool
    {
        return $this->renderDef->isStandalone();
    }
    
    public function getID() : string
    {
        return $this->renderDef->getElementID();
    }
    
    public function getLabel() : string
    {
        return $this->renderDef->getElementLabel();
    }
    
   /**
    * Retrieves the label with additional markup to 
    * ensure that it can word wrap correctly, even if
    * the label is filled with underscores.
    * 
    * @return string
    */
    public function getLabelForHTML() : string
    {
        return str_replace('_', '_<wbr/>', $this->getLabel());
    }
    
    public function getElementHTML() : string
    {
        return $this->elementHTML ?? $this->renderDef->getElementHTML();
    }

    /**
     * @param string $html
     * @return $this
     */
    public function setElementHTML(string $html) : self
    {
        $this->elementHTML = $html;
        return $this;
    }
    
    public function getDataType() : string
    {
        return $this->renderDef->getDataType();
    }
    
    public function getValue() : string
    {
        return $this->renderDef->getElementValue();
    }
    
    protected function _render()
    {
        return $this->renderContainer(
            $this->renderAddHTML(self::HTML_ABOVE_CONTROL).
            $this->renderControl().
            $this->renderError().
            $this->renderAddHTML(self::HTML_BELOW_CONTROL).
            $this->renderComment().
            $this->renderAddHTML(self::HTML_BELOW_COMMENT)
        );
    }
    
    public function isRequired() : bool
    {
        return $this->renderDef->isRequired();
    }

    public function isStructural() : bool
    {
        return $this->renderDef->isStructural();
    }
    
   /**
    * Adds a class to the form element's control 
    * group container DIV element.
    * 
    * @param string $name
    * @return UI_Form_Renderer_Element
    * 
    * @see UI_Form_Renderer_RenderType_Default
    */
    public function addControlGroupClass(string $name) : UI_Form_Renderer_Element
    {
        $this->renderType->addClass($name);
        
        return $this;
    }
    
    protected function renderContainer(string $html) : string
    {
        if($this->isStandalone())
        {
            return '<a id="'.$this->getID().'-anchor"></a>'.$html;
        }
        
        return
        '<a id="'.$this->getID().'-anchor"></a>'.
        '<label class="control-label" for="' . $this->getID() . '">' .
            '<span class="control-label-text">'.$this->getLabelForHTML() .'</span> ' .
            $this->renderIcons() .
        '</label>'.
        '<div class="controls">'.
            $html.
        '</div>';
    }
    
    protected function renderError() : string
    {
        return $this->renderType->renderMarkupError();
    }
    
    protected function renderComment() : string
    {
        return $this->renderType->renderMarkupComments();
    }
    
    protected function renderControl() : string
    {
        if($this->isFrozen())
        {
            $content = $this->getElementHTML();
            
            if($this->getDataType() == 'markup-editor') 
            {
                $content = $this->getValue();
            }
            
            return
            '<span class="control-value-frozen">' .
                $content .
            '</span>';
        }
        
        $content = $this->renderMarkupEditor();
        
        $append = $this->renderDef->getAttribute('data-append');
        $prepend = $this->renderDef->getAttribute('data-prepend');
        
        if(!empty($append) || !empty($prepend)) 
        {
            $classes = array();
            if(!empty($append)) {
                $classes[] = 'input-append';
                $append = '<span class="add-on">'.$append.'</span>';
            }
            
            if(!empty($prepend)) {
                $classes[] = 'input-prepend';
                $prepend = '<span class="add-on">'.$prepend.'</span>';
            }
            
            $content =
            '<div class="'.implode(' ', $classes).'">'.
                $prepend .
                $content .
                $append .
            '</div>';
        }
        
        return $content;
    }
    
    private function renderMarkupEditor() : string
    {
        $editor = $this->renderDef->getRuntimeProperty('markup-editor');
        
        if($editor instanceof UI_MarkupEditor)
        {
            return $editor->injectControlMarkup($this, $this->getElementHTML());
        }
     
        return $this->getElementHTML();
    }
    
    protected function renderIcons() : string
    {
        return
        '<span class="element-icons">'.
            implode(' ', $this->icons).
        '</span>';
    }
    
    protected function renderAddHTML($where) : string
    {
        if(!isset($this->injectHTML[$where])) {
            return '';
        }
        
        $html = '';
        
        foreach($this->injectHTML[$where] as $entry) 
        {
            if($this->isFrozen() && !$entry['whenFrozen']) {
                continue;
            }
            
            $html .= $entry['html'];
        }
        
        return $html;
    }
    
   /**
    * Converts one of the append or prepend HTML positions 
    * using the addHTML method, if it is present.
    * 
    * @param string $position
    * @param string $where
    * @see UI_Form::appendElementHTML()
    * @see UI_Form::prependElementHTML()
    */
    protected function initInjectPosition(string $position, string $where) : void
    {
        $items = $this->renderDef->getRuntimeProperty($position.'-html');
        if(!is_array($items)) {
            return;
        }
        
        foreach($items as $item) 
        {
            $this->addHTML((string)$item['html'], $where, $item['whenFrozen']);
        }   
    }
    
   /**
    * Adds custom HTML to the element at the specified position.
    * 
    * @param string $html
    * @param string $position
    * @param bool $whenFrozen Whether to add this when the element is frozen
    * @see UI_Form_Renderer_Element::HTML_BELOW_COMMENT
    * @see UI_Form_Renderer_Element::HTML_ABOVE_CONTROL
    * @see UI_Form_Renderer_Element::HTML_BELOW_CONTROL
    */
    public function addHTML(string $html, string $position=self::HTML_BELOW_CONTROL, bool $whenFrozen=false) : UI_Form_Renderer_Element
    {
        if(!isset($this->injectHTML[$position])) {
            $this->injectHTML[$position] = array();
        }
        
        $this->injectHTML[$position][] = array(
            'html' => $html,
            'whenFrozen' => $whenFrozen
        );
        
        return $this;
    }
    
    public function addHTMLBelowComment(string $html, bool $whenFrozen=false) : UI_Form_Renderer_Element
    {
        return $this->addHTML($html, self::HTML_BELOW_COMMENT, $whenFrozen); 
    }
    
    public function addHTMLBelowControl(string $html, bool $whenFrozen=false) : UI_Form_Renderer_Element
    {
        return $this->addHTML($html, self::HTML_BELOW_CONTROL, $whenFrozen);
    }

    public function addHTMLAboveControl(string $html, bool $whenFrozen=false) : UI_Form_Renderer_Element
    {
        return $this->addHTML($html, self::HTML_ABOVE_CONTROL, $whenFrozen);
    }
    
    protected function initIcons()
    {
        if($this->isFrozen())
        {
            return;
        }
        
        $display = 'none';
        
        if($this->isRequired()) 
        {
            $display = 'inline-block';
        }
        
        $this->addIcon(
            UI::icon()->required()
            ->makeDangerous()
            ->addClass('icon-required')
            ->cursorHelp()
            ->setStyle('display', $display)
            ->setTooltip(t('This field is required.'))
        );
        
        // structural element icon for form elements that trigger a new draft if changed.
        if($this->isStructural())
        {
            $this->addIcon(
                UI::icon()->structural()
                ->makeMuted()
                ->cursorHelp()
                ->setTooltip(
                    t('This field is structural:') . ' ' .
                    t('If changed and the item is currently published, a new draft will be created.')
                )
            );
        }
    }
    
   /**
    * Adds an icon that is shown next to the label of the element.
    * 
    * @param UI_Icon $icon
    * @return UI_Form_Renderer_Element
    */
    public function addIcon(UI_Icon $icon) : UI_Form_Renderer_Element
    {
        $this->icons[] = $icon;
        return $this;
    }
    
    public function getFormRenderer() : UI_Form_Renderer
    {
        return $this->formRenderer;
    }
}
    