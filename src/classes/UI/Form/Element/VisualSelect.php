<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_VisualSelect} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_VisualSelect
 */

declare(strict_types=1);

use HTML\QuickForm2\Element\Select\SelectOption;
use UI\ClientResourceCollection;
use UI\Form\Element\VisualSelect\ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption;
use UI\Traits\ScriptInjectableInterface;
use UI\Traits\ScriptInjectableTrait;

/**
 * Select element that lets the user choose an item from
 * an image gallery (icons, for example). Includes filtering
 * by search term, and choosing the text value from a traditional
 * select element.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method HTML_QuickForm2_Element_VisualSelect_Optgroup addOptgroup($label, $attributes = null)
 * @method VisualSelectOption addOption($text, $value, $attributes = null)
 * @method VisualSelectOption prependOption($text, $value, $attributes = null)
 *
 * @see template_default_ui_forms_elements_visual_select
 */
class HTML_QuickForm2_Element_VisualSelect extends HTML_QuickForm2_Element_Select implements ScriptInjectableInterface
{
    use ScriptInjectableTrait;

    public const ERROR_IMAGE_SET_ALREADY_EXISTS = 130901;

    public const PROPERTY_SORTING_ENABLED = 'sorting-enabled';
    public const PROPERTY_PLEASE_SELECT_LABEL = 'please-select-label';
    public const PROPERTY_PLEASE_SELECT_ENABLED = 'please-select';

    protected int $thumbnailSizeL = 80;
    protected int $thumbnailSizeS = 60;
    protected int $filterThreshold = 20;
    private bool $checkered = false;

    protected function initSelect()  : void
    {
        $this->setSortingEnabled();

        $this->optionContainer->setOptGroupClass(HTML_QuickForm2_Element_VisualSelect_Optgroup::class);
        $this->optionContainer->setOptionClass(VisualSelectOption::class);
    }

    protected function _injectUIScripts(ClientResourceCollection $collection) : void
    {
        template_default_ui_forms_elements_visual_select::injectJavascript($collection);
    }

    /**
     * Enables/disables the "Please select" entry to be able to
     * not choose any of the proposed images.
     *
     * @param boolean $enabled
     * @param string|number|UI_Renderable_Interface|NULL $selectLabel Optional when enabled: the label to use for the "Please select" image
     * @return HTML_QuickForm2_Element_VisualSelect
     * @throws UI_Exception
     */
    public function setPleaseSelectEnabled(bool $enabled=true, $selectLabel=null) : self
    {
        $this->setRuntimeProperty(self::PROPERTY_PLEASE_SELECT_ENABLED, $enabled);
        $this->setRuntimeProperty(self::PROPERTY_PLEASE_SELECT_LABEL, toString($selectLabel));
        return $this;
    }
    
    public function isPleaseSelectEnabled() : bool
    {
        return $this->getRuntimeProperty(self::PROPERTY_PLEASE_SELECT_ENABLED) === true;
    }

    /**
     * @return string
     * @throws UI_Exception
     * @see template_default_ui_forms_elements_visual_select
     */
    public function __toString()
    {
        $this->addPleaseSelect();

        return (string)UI::getInstance()
            ->createTemplate('ui/forms/elements/visual-select')
            ->setVar('html', parent::__toString())
            ->setVar('element', $this);
    }
    
    protected function addPleaseSelect() : void
    {
        if($this->hasPleaseSelect()) {
            return;
        }
        
        $this->getOptionContainer()->prependOption(
            $this->getPleaseSelectLabel(),
            '', 
            array(
                VisualSelectOption::ATTRIBUTE_PLEASE_SELECT => 'yes'
            )
        );
    }

    public function hasPleaseSelect() : bool
    {
        $options = $this->getOptionsFlat();

        foreach($options as $option)
        {
            if($option->isPleaseSelect()) {
                return true;
            }
        }

        return false;
    }

    public function getPleaseSelectLabel() : string
    {
        $label = $this->getRuntimeProperty(self::PROPERTY_PLEASE_SELECT_LABEL);
        if(empty($label)) {
            $label = t('Do not use any image');
        }

        return '['.$label.']';
    }

    /**
     * @param SelectOption[]|NULL $options
     * @param VisualSelectOption[]|NULL $result
     * @return VisualSelectOption[]
     */
    public function getOptionsFlat(array $options=null, array $result=null) : array
    {
        $initial = false;
        
        if($result === null) {
            $result = array();
        }
        
        if($options === null) {
            $initial = true;
            $options = $this->getOptionContainer()->getOptions();
        }
        
        foreach($options as $option) 
        {
            if($option instanceof HTML_QuickForm2_Element_Select_Optgroup)
            {
                $result = $this->getOptionsFlat($option->getOptions(), $result);
            }
            else if($option instanceof VisualSelectOption)
            {
                $result[] = $option;
            }
        }
        
        if($initial && $this->getRuntimeProperty(self::PROPERTY_SORTING_ENABLED) === true) {
            usort($result, static function(VisualSelectOption $a, VisualSelectOption $b) : int {
                if($a->getAttribute(VisualSelectOption::ATTRIBUTE_PLEASE_SELECT) === 'yes') {
                    return -1;
                }
                return strnatcasecmp($a->getLabel(), $b->getLabel());
            });
        }
        
        return $result;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setSortingEnabled(bool $enabled=true) : self
    {
        return $this->setRuntimeProperty(self::PROPERTY_SORTING_ENABLED, $enabled);
    }
    
    public function isGroupingEnabled() : bool
    {
        $options = $this->optionContainer->getOptions();
        
        foreach($options as $option)
        {
            if($option instanceof HTML_QuickForm2_Element_Select_Optgroup)
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function isFilteringEnabled() : bool
    {
        return $this->optionContainer->countOptions() >= $this->filterThreshold;
    }
    
   /**
    * Sets the default, large size of the thumbnails.
    * 
    * @param int $size
    * @return HTML_QuickForm2_Element_VisualSelect
    * @see HTML_QuickForm2_Element_VisualSelect::setSmallThunbnailSize()
    */
    public function setLargeThumbnailSize(int $size) : self
    {
        $this->thumbnailSizeL = $size;
        return $this;
    }

    /**
     * Sets the size of the thumbnails for long lists with
     * more items than the filtering threshold.
     *
     * @param int $size
     * @return HTML_QuickForm2_Element_VisualSelect
     * @see HTML_QuickForm2_Element_VisualSelect::setLargeThumbnailSize()
     */
    public function setSmallThumbnailSize(int $size) : self
    {
        $this->thumbnailSizeS = $size;
        return $this;
    }
    
   /**
    * Sets the amount of options from which the filtering
    * element will be shown.
    * 
    * @param int $amount
    * @return HTML_QuickForm2_Element_VisualSelect
    */
    public function setFilterThreshold(int $amount) : self
    {
        $this->filterThreshold = $amount;
        return $this;
    }

    public function getFilterThreshold() : int
    {
        return $this->filterThreshold;
    }

    /**
     * Adds a checkered background to the images, to be able to
     * see when they have transparency.
     *
     * @param bool $checkered
     * @return $this
     */
    public function makeCheckered(bool $checkered=true) : self
    {
        $this->checkered = $checkered;
        return $this;
    }

    public function isCheckered() : bool
    {
        return $this->checkered;
    }

    public function getThumbnailSize() : int
    {
        if($this->isFilteringEnabled()) {
            return $this->thumbnailSizeS;
        }
        
        return $this->thumbnailSizeL;
    }

    /**
     * Adds an image to select: simultaneously adds it to the
     * select element and the list of images.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $value
     * @param string $url
     * @param array<string,string> $attributes
     * @return VisualSelectOption
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    public function addImage($label, string $value, string $url, array $attributes=array()) : VisualSelectOption
    {
        $attributes[VisualSelectOption::ATTRIBUTE_IMAGE_URL] = $url;
        return $this->addOption($label, $value, $attributes);
    }

    /**
     * @var array<string,ImageSet>
     */
    private array $imageSets = array();

    public function addImageSet(string $id, string $label) : ImageSet
    {
        if(isset($this->imageSets[$id])) {
            throw new UI_Exception(
                'Image set already exists.',
                sprintf(
                    'Cannot add image set [%s], it has already been added.',
                    $id
                ),
                self::ERROR_IMAGE_SET_ALREADY_EXISTS
            );
        }

        $this->imageSets[$id] = new ImageSet($this, $id, $label);

        return $this->imageSets[$id];
    }

    /**
     * @return ImageSet[]
     */
    public function getImageSets() : array
    {
        return array_values($this->imageSets);
    }

    public function getActiveImageSet() : ?ImageSet
    {
        $sets = $this->getImageSets();

        if(empty($sets)) {
            return null;
        }

        return $sets[0];
    }

    /**
     * @return $this
     */
    public function makeBlock() : self
    {
        return $this->addClass('btn-block');
    }

    /**
     * @var string[]
     */
    protected array $containerClasses = array();
    
    /**
     * Adds a class to the container element of the button and dropdown menu.
     * Use this when you need to be able to style the dropdown menu, for example,
     * since by default it is not wrapped in another element.
     *
     * @param string $className
     * @return $this
     */
    public function addContainerClass(string $className) : self
    {
        if(!in_array($className, $this->containerClasses, true)) {
            $this->containerClasses[] = $className;
        }
        
        return $this;
    }

    /**
     * @return string[]
     */
    public function getContainerClasses() : array
    {
        return $this->containerClasses;
    }
}
