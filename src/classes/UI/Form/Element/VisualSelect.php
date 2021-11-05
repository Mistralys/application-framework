<?php
/**
 * File containing the {@link HTML_QuickForm2_Element_VisualSelect} class.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @custom
 */

/**
 * Bootstrap-based mutiple select element that implements the
 * interface of the bootstrap multiselect plugin.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method HTML_QuickForm2_Element_VisualSelect_Optgroup addOptgroup($label, $attributes = null)
 * 
 * @custom
 */
class HTML_QuickForm2_Element_VisualSelect extends HTML_QuickForm2_Element_Select
{
    public const ERROR_XXXXX = 33601;

    /**
     * @var int
     */
    protected $thumbnailSizeL = 80;

    /**
     * @var int
     */
    protected $thumbnailSizeS = 60;

    /**
     * @var int
     */
    protected $filterThreshold = 20;

    /**
     * @var bool
     */
    private $checkered;

    protected function initNode() 
    {
        $this->setSortingEnabled();
    }
    
   /**
    * Enables/disables the "Please select" entry to be able to
    * not choose any of the proposed images.
    * 
    * @param boolean $enabled
    * @param string $selectLabel Optional when enabled: the label to use for the "Please select" image
    * @return HTML_QuickForm2_Element_VisualSelect
    */
    public function setPleaseSelectEnabled($enabled=true, $selectLabel=null)
    {
        $this->setRuntimeProperty('please-select', $enabled);
        $this->setRuntimeProperty('please-select-label', $selectLabel);
        return $this;
    }
    
    public function isPleaseSelectEnabled()
    {
        return $this->getRuntimeProperty('please-select') === true;
    }
    
    public function __toString()
    {
        $ui = UI::getInstance();
        $ui->addJavascript('forms/visualselect.js');
        $ui->addStylesheet('forms/visualselect.css');
        
        $this->addClass('select-visualselect');
        $this->addContainerClass('visel-images');
        
        $id = $this->getAttribute('id');
        if (empty($id)) {
            $id = 'visel' . nextJSID();
            $this->setAttribute('id', $id);
        }
        
        $filteringEnabled = $this->isFilteringEnabled();
        $groupingEnabled = $this->isGroupingEnabled();
        
        if($filteringEnabled) {
            $this->addContainerClass('filtering-enabled');
        }
        
        if($this->isPleaseSelectEnabled()) {
            $this->addPleaseSelect();
        }
        
        $html = parent::__toString();
        
        if (!$this->frozen) 
        {
            $ui->addJavascriptOnload(sprintf(
                "new Forms_VisualSelect('%s')",
                $id
            ));
            
            $options = $this->getOptionContainer()->getOptions();
            
            ob_start();
            ?>
                <div class="<?php echo implode(' ', $this->containerClasses) ?>" id="<?php echo $id ?>-visel">
                    <div class="visel-toolbar">
                        <?php 
                            if($groupingEnabled) 
                            {
                                $group = UI::getInstance()->createButtonGroup();
                                $group->addButton(
                                    UI::button(t('Flat view'))
                                    ->setIcon(UI::icon()->flat())
                                    ->addClass('visel-btn-flat-view')
                                );
                                $group->addButton(
                                    UI::button(t('Grouped view'))
                                    ->setIcon(UI::icon()->grouped())
                                    ->addClass('visel-btn-grouped-view')
                                );
                                
                                echo $group;
                            }
                        
                            if($filteringEnabled) 
                            {
                                ?>
                                	<input type="text" value="" class="visel-filter-input" placeholder="<?php pt('Filter the list...') ?>">
                              	<?php
                              
                                echo UI::button()
                                ->setIcon(UI::icon()->delete())
                                ->setTooltipText(t('Clear the filter'))
                                ->addClass('visel-btn-clear-filter'); 
                            }
                        ?>
                    </div>
                    <div class="visel-body">
                        <ul class="visel-items grouped">
                            <?php 
                                $this->renderOptionsList($options);
                            ?>
                        </ul>
                        <ul class="visel-items flat">
                            <?php 
                                $this->renderOptionsList($this->getOptionsFlat());
                            ?>
                        </ul>
                    </div>
                    <div class="visel-expand">
                    	<?php echo UI::icon()->expand().' '.t('Expand/Collapse') ?>
                	</div>
                </div>
            <?php 
            $html .= ob_get_clean();
        }
        
        return $html;
    }
    
    protected function addPleaseSelect()
    {
        $options = $this->getOptionsFlat();
        
        $url = null;
        foreach($options as $option) {
            if(isset($option['attr']['image-url'])) {
                $url = $option['attr']['image-url'];
                break;
            }
        }
        
        $label = $this->getRuntimeProperty('please-select-label');
        if(empty($label)) {
            $label = t('Do not use any image');
        }
        
        $this->getOptionContainer()->prependOption(
            '['.$label.']', 
            '', 
            array('image-url' => $url)
        );
    }
    
    protected function getOptionsFlat($options=null, $result=null)
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
            if($option instanceof HTML_QuickForm2_Element_Select_Optgroup) {
                $result = $this->getOptionsFlat($option->getOptions(), $result);
                continue;
            }
            
            $result[] = $option;
        }
        
        if($initial && $this->getRuntimeProperty('sorting-enabled') === true) {
            usort($result, function($a, $b) {
                if($a['attr']['value'] === '') {
                    return -1;
                }
                return strnatcasecmp($a['text'], $b['text']);
            });
        }
        
        return $result;
    }
    
    public function setSortingEnabled($enabled=true)
    {
        $this->setRuntimeProperty('sorting-enabled', $enabled);
        return $this;
    }
    
    protected function isGroupingEnabled()
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
    
    public function isFilteringEnabled()
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
    public function setLargeThumbnailSize($size)
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
    public function setSmallThumbnailSize($size)
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
    public function setFilterThreshold($amount)
    {
        $this->filterThreshold = $amount;
        return $this;
    }

    /**
     * Adds a checkered background to the images, to be able to
     * see when they have transparency.
     *
     * @param bool $checkered
     * @return $this
     */
    public function makeCheckered(bool $checkered=true)
    {
        $this->checkered = $checkered;
        return $this;
    }

    protected function renderOptionsList($options)
    {
        foreach($options as $option)
        {
            if($option instanceof HTML_QuickForm2_Element_Select_Optgroup)
            {
                ?>
                    <li class="visel-group">
                        <h4 class="visel-group-header"><?php echo $option->getLabel() ?></h4>
                        <ul class="visel-items">
                            <?php 
                                $this->renderOptionsList($option->getOptions());
                            ?>
                        </ul>
                    </li>
                <?php 
            }
            else
            {
                if(!isset($option['attr']['image-url'])) {
                    continue;
                }
                
                $imgAtts = array(
                    'id' => nextJSID(),
                    'title' => $option['text'],
                    'alt' => $option['text'],
                    'src' => $option['attr']['image-url'],
                    'class' => 'visel-item-image',
                    'style' => 'width:'.$this->getThumbnailSize().'px'
                );

                JSHelper::tooltipify($imgAtts['id']);
                
                $class = 'visel-item';
                
                if($option['attr']['value'] === '') {
                    $class .= ' no-icon';
                }

                if($this->checkered) {
                    $class .= ' checkered';
                }

                ?>
                    <li class="<?php echo $class ?>" data-value="<?php echo $option['attr']['value'] ?>">
                        <img <?php echo compileAttributes($imgAtts) ?>/>
                    </li>
                <?php
            }
        }
    }
    
    protected function getThumbnailSize()
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
    * @param string $label
    * @param string $value
    * @param string $url
    */
    public function addImage($label, $value, $url)
    {
        $this->addOption($label, $value, array('image-url' => $url));
    }
    
    public function makeMultiple()
    {
        $this->setAttribute('multiple', 'multiple');
    }
    
    public function makeBlock()
    {
        $this->addClass('btn-block');
        return $this;
    }
    
    protected $containerClasses = array();
    
    /**
     * Adds a class to the container element of the button and dropdown menu.
     * Use this when you need to be able to style the dropdown menu, for example,
     * since by default it is not wrapped in another element.
     *
     * @param string $className
     * @return HTML_QuickForm2_Element_VisualSelect
     */
    public function addContainerClass($className)
    {
        if(!in_array($className, $this->containerClasses)) {
            $this->containerClasses[] = $className;
        }
        
        return $this;
    }
    
    public function loadOptions(array $options)
    {
        $this->possibleValues  = array();
        $this->optionContainer = new HTML_QuickForm2_Element_VisualSelect_OptionContainer(
            $this->values, $this->possibleValues
        );
        $this->loadOptionsFromArray($this->optionContainer, $options);
        return $this;
    }
}
