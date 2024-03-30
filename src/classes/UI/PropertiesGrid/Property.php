<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

abstract class UI_PropertiesGrid_Property implements UI_Interfaces_Conditional
{
    use UI_Traits_Conditional;

    protected UI_PropertiesGrid $grid;
    protected string $label;
    protected string $emptyText;

   /**
    * @var mixed
    */
    protected $text;
    
    /**
     * @var UI_Button[]
     */
    protected array $buttons = array();

    /**
     * @param UI_PropertiesGrid $grid
     * @param string|number|UI_Renderable_Interface $label
     * @param mixed $value
     * @throws UI_Exception
     */
    public function __construct(UI_PropertiesGrid $grid, $label, $value=null)
    {
        $this->grid = $grid;
        $this->label = toString($label);
        $this->text = $value;
        $this->emptyText = t('Empty');
        
        $this->init();
    }
    
    protected function init() : void
    {
        
    }
    
    protected function resolveText() : UI_StringBuilder
    {
        if($this->text !== '' && $this->text !== null) 
        {
            return $this->filterValue($this->text);
        }
        
        return $this->resolveEmptyText();
    }

    protected function resolveEmptyText() : UI_StringBuilder
    {
        return sb()->muted($this->emptyText);
    }

    public function render() : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $text = $this->resolveText();
        $label = sb()->add($this->label);
        
        if(!empty($this->comment)) 
        {
            $text->muted('- '.$this->comment);
        }
        
        if(!empty($this->helpText))
        {
            $label->icon(
                UI::icon()->help()
                    ->addClass('noprint')
                    ->makeInformation()
                    ->cursorHelp()
                    ->setTooltip($this->helpText)
            );
        }
        
        OutputBuffering::start();

        ?>
        <tr>
            <th class="align-right nowrap" style="width:<?php echo $this->grid->getLabelWidth() ?>%">
                <?php echo $label ?>
            </th>
            <td>
                <?php echo $this->renderButtons() ?>
                <?php echo $text ?>
            </td>
        </tr>
        <?php

        return OutputBuffering::get();
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
     * @throws UI_Exception
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

    /**
     * @var string
     */
    protected string $comment = '';

    /**
     * Typically shown inline next to the content of the property.
     *
     * @param string|number|UI_Renderable_Interface|NULL $comment
     * @return $this
     * @throws UI_Exception
     */
    public function setComment($comment) : self
    {
        $this->comment = toString($comment);
        return $this;
    }

    /**
     * @var string
     */
    protected string $helpText = '';

    /**
     * This text is typically shown with a help icon, and available by
     * clicking on it.
     *
     * @param string|number|UI_Renderable_Interface|NULL $help
     * @return $this
     * @throws UI_Exception
     */
    public function setHelpText($help) : self
    {
        $this->helpText = toString($help);
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
