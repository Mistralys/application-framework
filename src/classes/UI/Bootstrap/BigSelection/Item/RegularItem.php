<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection\Item;

use AppUtils\AttributeCollection;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OutputBuffering;
use UI\AdminURLs\AdminURLInterface;
use UI\Bootstrap\BigSelection\BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS;
use UI_Exception;
use UI_Renderable_Interface;

/**
 */
class RegularItem extends BaseItem
{
    public const string ATTRIBUTE_DESCRIPTION = 'description';
    public const string ATTRIBUTE_HREF = 'href';
    public const string ATTRIBUTE_ONCLICK = 'onclick';

    protected string $label = '';

    /**
     * @var array<int,array{control:string,attributes:AttributeCollection|null}>
     */
    protected array $metaControls = array();

    /**
     * Changes the label after instantiating the item.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @return RegularItem
     * @throws UI_Exception
     */
    public function setLabel($label): RegularItem
    {
        $this->label = toString($label);
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets a description that will be shown along with the label.
     *
     * @param string|number|UI_Renderable_Interface $text
     * @return RegularItem
     * @throws UI_Exception
     */
    public function setDescription($text): RegularItem
    {
        $this->setAttribute(self::ATTRIBUTE_DESCRIPTION, toString($text));
        return $this;
    }

    public function getDescription(): string
    {
        return (string)$this->getAttribute(self::ATTRIBUTE_DESCRIPTION);
    }

    protected function _render(): string
    {
        $anchorAtts = array(
                'href' => $this->getAttribute(self::ATTRIBUTE_HREF),
                'onclick' => $this->getAttribute(self::ATTRIBUTE_ONCLICK)
        );

        $this->addClass(self::CLASS_NAME_ENTRY);

        $searchAtt = '';

        if ($this->parent->isFilteringInUse()) {
            $searchAtt = ' data-terms="' . $this->resolveSearchWords() . '"';
        }

        OutputBuffering::start();

        ?>
        <li class="<?php echo implode(' ', $this->classes) ?>"<?php echo $searchAtt ?>>
            <?php $this->renderMetaControls() ?>
            <a<?php echo compileAttributes($anchorAtts) ?> class="<?php echo BigSelectionCSS::ANCHOR ?>">
                <span class="<?php echo BigSelectionCSS::LABEL ?>">
                    <?php echo $this->renderLabel() ?>
                </span>
                <span class="<?php echo BigSelectionCSS::DESCRIPTION ?>">
                    <?php echo $this->getAttribute(self::ATTRIBUTE_DESCRIPTION) ?>
                </span>
            </a>
        </li>
        <?php

        return OutputBuffering::get();
    }

    /**
     * Adds a control to the meta area of the item (typically floating on the right side).
     *
     * @param string|StringableInterface $control
     * @param AttributeCollection|null $attributes Optional attributes for the meta-control element.
     * @return $this
     */
    public function addMetaControl($control, ?AttributeCollection $attributes = null): self
    {
        $this->metaControls[] = array(
                'control' => (string)$control,
                'attributes' => $attributes
        );

        return $this;
    }

    protected function renderMetaControls(): void
    {
        if (empty($this->metaControls)) {
            return;
        }

        ?>
        <ul class="<?php echo BigSelectionCSS::META_CONTROLS_LIST ?> unstyled">
            <?php
            foreach ($this->metaControls as $control) {
                if (isset($control['attributes'])) {
                    $attributes = $control['attributes']->render();
                } else {
                    $attributes = AttributeCollection::create();
                }

                $attributes->addClass(BigSelectionCSS::META_CONTROL_ITEM);

                ?>
                <li <?php echo $attributes ?>>
                    <?php echo $control['control'] ?>
                </li>
                <?php
            }
            ?>
        </ul>
        <?php
    }

    protected function resolveSearchWords(): string
    {
        $words = strip_tags($this->label);

        $descr = $this->getDescription();
        if (!empty($descr)) {
            $words .= ' ' . strip_tags($descr);
        }

        return str_replace(array('"'), " ", $words);
    }

    protected function renderLabel(): string
    {
        $label = $this->label;

        if (isset($this->icon)) {
            $label = $this->icon . ' ' . $label;
        }

        return $label;
    }

    /**
     * @param string|AdminURLInterface $url
     * @return $this
     */
    public function makeLinked($url): self
    {
        return $this->setAttribute(self::ATTRIBUTE_HREF, (string)$url);
    }

    /**
     * @return $this
     */
    public function makeActive(): self
    {
        return $this->addClass(BigSelectionCSS::STATE_ACTIVE);
    }

    public function makeClickable($statement): RegularItem
    {
        $this->setAttribute(self::ATTRIBUTE_ONCLICK, $statement);
        $this->setAttribute(self::ATTRIBUTE_HREF, 'javascript:void(0)');
        return $this;
    }
}
