<?php

abstract class UI_PropertiesGrid_Property implements UI_Interfaces_Conditional
{
    use UI_Traits_Conditional;

   /**
    * @var UI_PropertiesGrid
    */
    protected $grid;
    
   /**
    * @var string
    */
    protected $label;
    
   /**
    * @var mixed
    */
    protected $text;
    
   /**
    * @var string
    */
    protected $emptyText = '';

    /**
     * @var UI_Button[]
     */
    protected $buttons = array();

    /**
    * @param UI_PropertiesGrid $grid
    * @param string|number|UI_Renderable_Interface $label
    * @param mixed $value
    */
    public function __construct(UI_PropertiesGrid $grid, $label, $value=null)
    {
        $this->grid = $grid;
        $this->label = toString($label);
        $this->text = $value;
        
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
    protected function resolveText() : UI_StringBuilder
    {
        if($this->text !== '' && $this->text !== null) 
        {
            return $this->filterValue($this->text);
        }
        
        return sb()->add($this->emptyText);
    }
    
    public function render() : string
    {
        if(!$this->isValid()) {
            return '';
        }

        $text = $this->resolveText();
        $label = sb()->add($this->label);
        
        if(!empty($this->comment)) 
        {
            $text->muted('- '.$this->comment);
        }
        
        if(isset($this->helpText)) 
        {
            $label->icon(
                UI::icon()->help()
                    ->addClass('noprint')
                    ->makeInformation()
                    ->cursorHelp()
                    ->setTooltip($this->helpText)
            );
        }
        
        $html =
        '<tr>'.
            '<th class="align-right nowrap" style="width:'.$this->grid->getLabelWidth().'%">'.
                $label.
            '</th>'.
            '<td>'.
                $this->renderButtons().
                $text.
            '</td>'.
        '</tr>';
        
        return $html;
    }
    
    protected function renderButtons() : string
    {
        if(empty($this->buttons))
        {
            return '';
        }
        
        $buttons =
        '<div class="btn-group pull-right">';
            foreach($this->buttons as $button) 
            {
                $buttons .= $button->render();
            }
            $buttons .=
        '</div>';
            
        return $buttons;
    }
    
   /**
    * Selects the text to show instead of the text if it is empty.
    * @param string|number|UI_Renderable_Interface $text
    * @return $this
    */
    public function ifEmpty($text)
    {
        $this->emptyText = toString($text);
        return $this;
    }

    /**
     * @param UI_Button $button
     * @return $this
     * @throws Application_Exception
     */
    public function addButton(UI_Button $button)
    {
        $button->makeMini();
        $this->buttons[] = $button;
        return $this;
    }
    
    protected $comment;
    
   /**
    * Typcially shown inline next to the content of the property.
    *  
    * @param string $comment
    * @return $this
    */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }
    
    protected $helpText;
    
   /**
    * This text is typically shown with a help icon, and available by
    * clicking on it.
    * 
    * @param string $help
    * @return $this
    */
    public function setHelpText($help)
    {
        $this->helpText = $help;
        return $this;
    }
    
   /**
    * Filters the value to be displayed according to its type.
    * 
    * @param mixed $value
    * @return UI_StringBuilder
    */
    abstract protected function filterValue($value) : UI_StringBuilder;
}
