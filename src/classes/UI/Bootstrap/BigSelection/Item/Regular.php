<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

/**
 */
class UI_Bootstrap_BigSelection_Item_Regular extends UI_Bootstrap_BigSelection_Item
{
    const ATTRIBUTE_DESCRIPTION = 'description';
    const ATTRIBUTE_HREF = 'href';
    const ATTRIBUTE_ONCLICK = 'onclick';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * Changes the label after instantiating the item.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @return UI_Bootstrap_BigSelection_Item_Regular
     * @throws UI_Exception
     */
    public function setLabel($label) : UI_Bootstrap_BigSelection_Item_Regular
    {
        $this->label = toString($label);
        return $this;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * Sets a description that will be shown along with the label.
     *
     * @param string|number|UI_Renderable_Interface $text
     * @return UI_Bootstrap_BigSelection_Item_Regular
     * @throws UI_Exception
     */
    public function setDescription($text) : UI_Bootstrap_BigSelection_Item_Regular
    {
        $this->setAttribute(self::ATTRIBUTE_DESCRIPTION, toString($text));
        return $this;
    }

    public function getDescription() : string
    {
        return (string)$this->getAttribute(self::ATTRIBUTE_DESCRIPTION);
    }

    protected function _render() : string
    {
        $anchorAtts = array(
            'href' => $this->getAttribute(self::ATTRIBUTE_HREF),
            'onclick' => $this->getAttribute(self::ATTRIBUTE_ONCLICK)
        );

        $this->addClass(self::CLASS_NAME_ENTRY);

        $searchAtt = '';

        if($this->parent->isFilteringInUse())
        {
            $searchAtt = ' data-terms="'.$this->resolveSearchWords().'"';
        }

        OutputBuffering::start();

        ?>
        <li class="<?php echo implode(' ', $this->classes) ?>"<?php echo $searchAtt ?>>
            <a<?php echo compileAttributes($anchorAtts) ?> class="bigselection-anchor">
        			<span class="bigselection-label">
        				<?php echo $this->renderLabel() ?>
    				</span>
                <span class="bigselection-description">
    					<?php echo $this->getAttribute(self::ATTRIBUTE_DESCRIPTION) ?>
    				</span>
            </a>
        </li>
        <?php

        return OutputBuffering::get();
    }

    protected function resolveSearchWords() : string
    {
        $words = strip_tags($this->label);

        $descr = $this->getDescription();
        if(!empty($descr))
        {
            $words .= ' '.strip_tags($descr);
        }

        return str_replace(array('"'), " ", $words);
    }

    protected function renderLabel() : string
    {
        $label = $this->label;

        if(isset($this->icon)) {
            $label = $this->icon.' '.$label;
        }

        return $label;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function makeLinked(string $url) : self
    {
        return $this->setAttribute(self::ATTRIBUTE_HREF, $url);
    }

    /**
     * @return $this
     */
    public function makeActive() : self
    {
        return $this->addClass('active');
    }

    public function makeClickable($statement) : UI_Bootstrap_BigSelection_Item_Regular
    {
        $this->setAttribute(self::ATTRIBUTE_ONCLICK, $statement);
        $this->setAttribute(self::ATTRIBUTE_HREF, 'javascript:void(0)');
        return $this;
    }
}
