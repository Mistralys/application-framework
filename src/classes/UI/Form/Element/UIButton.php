<?php
/**
 * File containing the class {@see UI_Form_Element_UIButton}.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see UI_Form_Element_UIButton
 */

declare(strict_types=1);

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Form_Element_UIButton extends HTML_QuickForm2_Element_Button
{
    /**
     * NOTE: We are not implementing the button interface
     * on purpose. The interface conflicts with methods from
     * the QuickForm classes (like hasClass() for example).
     * This way we are at least able to use the trait.
     *
     * @see UI_Interfaces_Button::hasClass()
     * @see HTML_Common2::hasClass()
     */
    use UI_Traits_GenericButton;

    private UI_Button $button;

    protected function initNode() : void
    {
        parent::initNode();
        
        $this->button = UI::button();
    }

    public function getButtonInstance() : UI_Button
    {
        return $this->button;
    }
}
