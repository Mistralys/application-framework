<?php

/**
 * @method UI_Bootstrap_BigSelection_Item setIcon($icon) setIcon(UI_Icon $icon)
 * @property UI_Bootstrap_BigSelection $parent 
 */
class UI_Bootstrap_BigSelection_Item extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
   /**
    * @var string
    */
    protected $label;
    
   /**
    * Changes the label after instantiating the item.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @return UI_Bootstrap_BigSelection_Item
    */
    public function setLabel($label) : UI_Bootstrap_BigSelection_Item
    {
        $this->label = toString($label);
        return $this;
    }
    
   /**
    * Sets a description that will be shown along with the label.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Bootstrap_BigSelection_Item
    */
    public function setDescription($text)
    {
        $this->setAttribute('description', toString($text));
        return $this;
    }
        
    protected function _render()
    {
        $anchorAtts = array(
            'href' => $this->getAttribute('href'),
            'onclick' => $this->getAttribute('onclick')
        );
        
        $this->addClass('bigselection-entry');
        
        $searchAtt = '';
        
        if($this->parent->isFilteringInUse())
        {
            $searchAtt = ' data-terms="'.$this->resolveSearchWords().'"';
        }
        
        ob_start();
        
        ?>
        	<li class="<?php echo implode(' ', $this->classes) ?>"<?php echo $searchAtt ?>>
        		<a<?php echo compileAttributes($anchorAtts) ?> class="bigselection-anchor">
        			<span class="bigselection-label">
        				<?php echo $this->renderLabel() ?>
    				</span>
    				<span class="bigselection-description">
    					<?php echo $this->getAttribute('description') ?>
    				</span>
        		</a>
        	</li>
    	<?php
    	
    	return ob_get_clean();
    }
    
    protected function resolveSearchWords() : string
    {
        $words = strip_tags($this->label);
        
        $descr = $this->getAttribute('description');
        if(!empty($descr)) 
        {
            $words .= ' '.strip_tags($descr);
        }
        
        $words = str_replace(array('"'), " ", $words);
        
        return $words;
    }
    
    protected function renderLabel()
    {
        $label = $this->label;
        
        if(isset($this->icon)) {
            $label = $this->icon.' '.$label;
        }
        
        return $label;
    }
    
    public function makeLinked(string $url)
    {
        $this->setAttribute('href', $url);
        return $this;
    }
    
    public function makeClickable($statement)
    {
        $this->setAttribute('onclick', $statement);
        $this->setAttribute('href', 'javascript:void(0)');
        return $this;
    }
}
