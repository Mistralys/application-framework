<?php
/**
 * File containing the {@link UI_QuickSelector} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_QuickSelector
 */

use AppUtils\Traits_Classable;
use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;
use AppUtils\Interface_Classable;

/**
 * UI helper class for creating quick item selection elements with
 * integrated next/previous elements.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_QuickSelector extends UI_QuickSelector_Container implements UI_Renderable_Interface, Interface_Classable, Interface_Optionable, Application_Interfaces_Iconizable
{
    use Traits_Classable;
    use UI_Traits_RenderableGeneric;
    use Traits_Optionable;
    use Application_Traits_Iconizable;

    public const ERROR_UNKNOWN_LAYOUT_PART = 24901;
    
   /**
    * @var UI
    */
    protected $ui;
    
   /**
    * @var string
    */
    protected $jsName;

    public function __construct(string $id='')
    {
        if(empty($id)) {
            $id = nextJSID();
        }

        $this->ui = $this->getUI();
        $this->jsName = 'qs'.str_replace('-', '', $id);

        parent::__construct($this, $id, '');
    }

    public function getDefaultOptions(): array
    {
        return array(
            'sorting' => false,
            'show-buttons' => true,
            'size' => 'default',
            'item-label-singular' => '',
            'item-label-plural' => ''
        );
    }

    /**
    * @var string
    */
    protected $selectedItemID = '';
    
   /**
    * Sets the pre-selected item from the list. Default is the first item in the list.
    * 
    * @param string $id
    * @return UI_QuickSelector
    */
    public function setSelectedItem(string $id) : UI_QuickSelector
    {
        $this->selectedItemID = $id;
        return $this;
    }
    
   /**
    * Whether to enable automatic sorting of the entries (by their label).
    * @param bool $enable
    * @return UI_QuickSelector
    */
    public function enableSorting(bool $enable=true) : UI_QuickSelector
    {
        return $this->setOption('sorting', $enable);
    }
    
   /**
    * Creates an ID that is unique for the quick selector, for
    * use in HTML element id attributes. 
    * 
    * @param string $part 
    * @return string
    */
    protected function elementID(string $part='') : string
    {
        $id = 'qs'.$this->id;
        
        if(!empty($part)) 
        {
            $id .= '_'.$part;
        }
        
        return $id;
    }
    
   /**
    * Disables the previous/next buttons, so that only the selector itself is shown.
    * 
    * @return UI_QuickSelector
    */
    public function disableButtons() : UI_QuickSelector
    {
        return $this->setOption('show-buttons', false);
    }
    
   /**
    * Disables the "Quick switch" label in front of the selector.
    * @return UI_QuickSelector
    */
    public function disableLabel() : UI_QuickSelector
    {
        return $this->setPartEnabled('label', false);
    }
    
   /**
    * Sets the label for the types of items in the list. This is
    * used in tooltips for example, when the text makes reference
    * to the items.
    * 
    * For example, if the list contained apples, you would use this
    * method to specify the item type:
    * 
    * setItemTypeLabel('apple', 'apples');
    * 
    * @param string $singular
    * @param string $plural
    * @return UI_QuickSelector
    */
    public function setItemTypeLabel(string $singular, string $plural) : UI_QuickSelector
    {
        $this->setOption('item-label-singular', $singular);
        $this->setOption('item-label-plural', $plural);

        return $this;
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
    public function getJSName() : string
    {
        return $this->jsName;
    }
    
    public function render() : string
    {
        return $this->_render();
    }

    public function isSortingEnabled() : bool
    {
        return $this->getBoolOption('sorting');
    }

    private function resolveLabel() : string
    {
        $result = sb()->add(strval($this->getIcon()));

        $label = $this->getLabel();

        if(empty($label))
        {
            $label = t('Quick switch');
        }

        if(!$this->isPartEnabled('label'))
        {
            $label = '';
        }

        $result->add($label);

        return (string)$result;
    }

    protected function _render() : string
    {
        $items = $this->getItems();

        if(empty($items) || count($items) === 1)
        {
            return '';
        }
        
        if($this->isSortingEnabled()) {
            usort($this->items, array($this, 'handle_sortItems'));
        }
        
        $jsName = $this->getJSName();
        
        $this->ui->addJavascript('ui/quickselector.js');
        $this->ui->addStylesheet('ui/quickselector.css');
        
        $this->ui->addJavascriptHead(sprintf(
            "var %s = new UI_QuickSelector(%s)",
            $this->getJSName(),
            json_encode($this->getID())
        ));
        
        $this->ui->addJavascriptOnload(sprintf("%s.Start()", $jsName));

        $classes = array(
            'quick-selector',
            'input-append',
            'size-'.$this->getStringOption('size')
        );

        $label = $this->resolveLabel();
        if(!empty($label)) {
            $classes[] = 'input-prepend';
        }
        
        $classes = array_merge($classes, $this->getClasses());

        $html =
        '<form id="'.$this->elementID().'" class="form-inline">'.
            '<div class="'.implode(' ', $classes).'">';

                if(!empty($label))
                {
                    $html .=
                    '<span class="add-on" id="'.$this->elementID('label').'">'.
                        $label.
                    '</span>';
                }
                $html .=
                '<select id="'.$this->elementID('select').'" onchange="'.$jsName.'.Switch()" class="quick-selector-select">';
                    $total = count($this->items);
                    for($i=0; $i<$total; $i++) {
                        $item = $this->items[$i];
                        
                        $html .= $item->render();
                    }
                    $html .=
                '</select>';
                if($this->getBoolOption('show-buttons'))
                {
                    if($this->isPartEnabled('buttonLeft')) {
                        $btn = UI::button()
                            ->setID($this->elementID('btn_prev'))
                            ->click($jsName.'.Previous()')
                            ->setIcon(UI::icon()->previous())
                            ->setTooltipText(t('Jump to the previous %1$s.', $this->getTypeSingular()));
                        
                        if($this->getStringOption('size') === 'small') {
                            $btn->makeSmall();
                        }
                        
                        $html .= $btn->render();
                    }
                    
                    if($this->isPartEnabled('buttonRight')) {
                        $btn = 
                        UI::button()
                            ->setID($this->elementID('btn_next'))
                            ->click($jsName.'.Next()')
                            ->setIcon(UI::icon()->next())
                            ->setTooltipText(t('Jump to the next %1$s.', $this->getTypeSingular()));
                        
                        if($this->getStringOption('size') === 'small') {
                            $btn->makeSmall();
                        }
                        
                        $html .= $btn->render();
                    }
                }
                $html .=     
            '</div>'.
		'</form>';
                
        return $html;
    }
    
    public function getSelectedID() : string
    {
        $selectedID = $this->selectedItemID;
        if(empty($selectedID) || !$this->hasItemID($selectedID)) {
            $selectedID = '';
        }
        
        return $selectedID;
    }
    
    protected function getTypeSingular() : string
    {
        $label = $this->getStringOption('item-label-singular');

        if(!empty($label))
        {
            return $label;
        }

        return t('item');
    }
    
    protected function getTypePlural() : string
    {
        $label = $this->getStringOption('item-label-plural');

        if(!empty($label))
        {
            return $label;
        }

        return t('items');
    }
    
   /**
    * Makes the selector more compact: removes the "Quick select" label 
    * in front, and makes the selector small-sized.
    * 
    * @return UI_QuickSelector
    */
    public function makeCompact() : UI_QuickSelector
    {
        $this->setPartEnabled('label', false);
        $this->makeSmall();
        
        return $this;
    }
    
   /**
    * @var array<string,bool>
    */
    protected $partStates = array(
        'label' => true,
        'buttonLeft' => true,
        'buttonRight' => true
    );
    
    public function setPartEnabled(string $part, bool $enabled=true) : UI_QuickSelector
    {
        $this->requirePart($part);
        
        $this->partStates[$part] = $enabled;
        return $this;
    }
    
    protected function requirePart(string $part)
    {
        if(isset($this->partStates[$part])) {
            return;
        }
        
        throw new Application_Exception(
            'Unknown part',
            sprintf(
                'Unknown quick selector layout part [%s]. Available parts are [%s].',
                $part,
                implode(', ', array_keys($this->partStates))    
            ),
            self::ERROR_UNKNOWN_LAYOUT_PART
        );    
    }
    
    public function isPartEnabled(string $part) : bool
    {
        $this->requirePart($part);
        
        return $this->partStates[$part];
    }
    
    public function makeSmall() : UI_QuickSelector
    {
        return $this->setOption('size', 'small');
    }
}
