<?php
/**
 * File containing the class {@see HTML_QuickForm2_Element_ExpandableSelect}.
 *
 * @package Application
 * @subpackage Forms
 * @see HTML_QuickForm2_Element_ExpandableSelect
 */

declare(strict_types=1);

/**
 * Multiple selection select element with integrated controls
 * to select and deselect elements, as well as to expand or
 * collapse the select to show or hide elements.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_ExpandableSelect extends HTML_QuickForm2_Element_Select
{
    private int $maxSize = 20;

    protected function initNode() : void
    {
        parent::initNode();

        $this->setAttribute('multiple');
    }

    /**
     * Sets the maximum amount of elements to show in the
     * select element.
     *
     * @param int $size
     * @return $this
     */
    public function setMaxSize(int $size) : self
    {
        $this->maxSize = $size;
        return $this;
    }

    public function getMaxSize() : int
    {
        return $this->maxSize;
    }

    public function getSize() : int
    {
        $minSize = $this->getMaxSize();
        $total = $this->countOptions(false);

        if($total < $minSize)
        {
            $minSize = $total;
        }

        return $minSize;
    }

    public function __toString() : string
    {
        $this->setAttribute('size', $this->getSize());

        return
            parent::__toString().
            '<div class="expandable-select-controls">'.
                $this->renderControls().
            '</div>';
    }

    private function renderControls() : string
    {
        $total = $this->countOptions(false);

        if($total <= 1)
        {
            return '';
        }

        return
            $this->renderSelectionControls().
            $this->renderCollapseControls();
    }

    private function renderSelectionControls() : string
    {
        $elID = $this->getId();

        $allStatement = sprintf("$('#%s option').prop('selected', true);$(this).blur();return false;", $elID);
        $noneStatement = sprintf("$('#%s option').prop('selected', false);$(this).blur();return false;", $elID);

        return (string)sb()
            ->add(sprintf(
                '<a href="#" onclick="%s">%s</a>',
                $allStatement,
                sb()->icon(UI::icon()->selectAll())->t('Select all')
            ))
            ->add('|')
            ->add(sprintf(
                '<a href="#" onclick="%s">%s</a>',
                $noneStatement,
                sb()->icon(UI::icon()->deselectAll())->t('Select none')
            ));
    }

    private function renderCollapseControls() : string
    {
        $total = $this->countOptions(false);

        if($total <= $this->getMaxSize())
        {
            return '';
        }

        $elID = $this->getId();

        $expandStatement = sprintf("$('#%s').attr('size', $('#%s option').length);$(this).blur();return false;", $elID, $elID);
        $collapseStatement = sprintf("$('#%s').attr('size', %s);$(this).blur();return false;", $elID, $this->getMaxSize());

        return (string)sb()
            ->add('&nbsp;')
            ->add('&nbsp;')
            ->add('&nbsp;')
            ->add(sprintf(
                '<a href="#" onclick="%s">%s</a>',
                $expandStatement,
                sb()->icon(UI::icon()->caretDown())->t('Expand')
            ))
            ->add('|')
            ->add(sprintf(
                '<a href="#" onclick="%s">%s</a>',
                $collapseStatement,
                sb()->icon(UI::icon()->caretUp())->t('Collapse')
            ))
            ->nl();
    }
}
