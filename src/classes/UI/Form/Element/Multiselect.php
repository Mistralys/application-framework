<?php
/**
 * File containing the {@link HTML_QuickForm2_Element_Multiselect} class.
 *
 * @package Application
 * @subpackage Forms
 * @see HTML_QuickForm2_Element_Multiselect
 */

/**
 * Bootstrap-based multiple select element that implements the
 * interface of the bootstrap multiselect plugin.
 *
 * @package Application
 * @subpackage Forms
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see https://github.com/davidstutz/bootstrap-multiselect
 * @see http://davidstutz.github.io/bootstrap-multiselect
 */
class HTML_QuickForm2_Element_Multiselect extends HTML_QuickForm2_Element_Select
{
    public function __toString()
    {
        $this->initTemplates();
        
        $ui = UI::getInstance();
        $ui->addJavascript('bootstrap-multiselect.js');
        $ui->addStylesheet('bootstrap-multiselect.css');
        $ui->addJavascript('forms/multiselect.js');

        $this->addClass('select-multiselect');
        $this->addContainerClass('btn-group');
        $this->addContainerClass('multiselect');

        if($this->hasClass('btn-block'))
        {
            $this->addContainerClass('multiselect-block');
        }

        $id = $this->getAttribute('id');
        if (empty($id)) {
            $id = 'multi' . nextJSID();
            $this->setAttribute('id', $id);
        }

        $html = parent::__toString();

        if ($this->frozen)
        {
            return $html;
        }

        $classes = explode(' ', $this->getAttribute('class'));
        $classes[] = 'btn';

        $this->multiOptions['buttonClass'] = implode(' ', $classes);
        $this->multiOptions['selectAllText'] = t('Select all');
        $this->multiOptions['filterPlaceholder'] = $this->getFilterPlaceholder();
        $this->multiOptions['templates'] = array();
        $this->multiOptions['buttonContainer'] = '<div class="'.implode(' ', $this->containerClasses).'" />';

        foreach($this->templates as $template => $code)
        {
            $this->multiOptions['templates'][$template] = $code;
        }

        if (!empty($this->multiOptions)) {
            $options = json_encode($this->multiOptions);
        } else {
            $options = '{}';
        }

        $optid = 'opt' . nextJSID();
        $ui->addJavascriptOnload('var ' . $optid . ' = ' . $options);

        $ui->addJavascriptOnload($optid . '.buttonText = MultiSelect.Render_Label');

        $ui->addJavascriptOnload(
            "$('#" . $id . "').multiselect(" . $optid . ")"
        );

        return $html;
    }
    
    protected $templates = array(
        'filter' => null,
        'filterClearBtn' => null,
    );
    
    protected function initTemplates()
    {
        if(!isset($this->templates['filter'])) {
            $this->templates['filter'] = 
            '<li class="multiselect-item filter">'.
                '<div class="input-group input-prepend input-append">'.
                    '<span class="input-group-addon add-on">'.
                        UI::icon()->search().
                    '</span>'.
                    '<input class="form-control multiselect-search" type="text">'.
                '</div>'.
            '</li>';
        }
        
        if(!isset($this->templates['filterClearBtn'])) {
            $this->templates['filterClearBtn'] = 
            '<span class="input-group-btn add-on">'.
                '<button class="btn btn-link multiselect-clear-filter clear-search" type="button">'.
                    UI::icon()->delete().
                '</button>'.
            '</span>';
        }
    }
    
    protected $multiOptions = array();

    protected $filterPlaceholder;

    public function setFilterPlaceholder($text) 
	{
        $this->filterPlaceholder = $text;
    }
	
	protected function getFilterPlaceholder()
	{
		if(isset($this->filterPlaceholder)) {
			return $this->filterPlaceholder;
		}
		
		return t('Search');
	}

    public function enableFiltering()
    {
        return $this->setMultiOption('enableCaseInsensitiveFiltering', true);
    }

    public function setMaxHeight($height)
    {
        return $this->setMultiOption('maxHeight', $height);
    }

    public function setMultiOption($name, $value)
    {
        $this->multiOptions[$name] = $value;
        return $this;
    }
    
    public function makeBlock()
    {
        $this->addClass('btn-block');
        return $this;
    }

    public function enableSelectAll()
    {
        return $this->setMultiOption('includeSelectAllOption', true);
    }
    
   /**
    * When the element is shown inline, the dropdown menu is 
    * opened as a block element in the page, not as a hover 
    * menu.
    */
    public function makeInline()
    {
        return $this->addContainerClass('inline-menu');
    }

    /**
     * @var string[]
     */
    protected $containerClasses = array();
    
   /**
    * Adds a class to the container element of the button and dropdown menu.
    * Use this when you need to be able to style the dropdown menu, for example,
    * since by default it is not wrapped in another element.
    * 
    * @param string $className
    * @return HTML_QuickForm2_Element_Multiselect
    */
    public function addContainerClass($className)
    {
        if(!in_array($className, $this->containerClasses)) {
            $this->containerClasses[] = $className;
        }
        
        return $this;
    }
}