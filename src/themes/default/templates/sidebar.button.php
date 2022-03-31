<?php
/**
 * File containing the template class {@see template_default_frame}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame
 */

declare(strict_types=1);

use AppUtils\Interface_Classable;
use AppUtils\Traits_Classable;

/**
 * Main template for the frame skeleton of all pages.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_sidebar_button extends UI_Page_Template_Custom implements Interface_Classable
{
    use Traits_Classable;

    protected function generateOutput() : void
    {
        $this->configureAction();
        $this->configureLocking();
        $this->configureDisabled();
        $this->configureMode();
        $this->configureTagName();

        $this->setAttribute('class', $this->classesToString());

        if(isset($this->menu)) {
            ?>
                <span style="position:relative">
            <?php
        }
        ?>
            <<?php echo $this->tagname ?> <?php echo compileAttributes($this->attributes) ?>>
                <?php echo $this->renderLabel() ?>
            </<?php echo $this->tagname ?>>
        <?php

        if(isset($this->menu)) {
            ?>
                    <?php echo $this->menu->render() ?>
                </span>
            <?php
        }
    }

    private function renderLabel() : string
    {
        $label = $this->button->getLabel();
        if ($this->design == 'developer') {
            $label = '<b>' . t('DEV:') . '</b> ' . $label;
        }

        if ($this->button instanceof UI_Page_Sidebar_Item_DropdownButton && $this->button->hasCaret()) {
            $label .= ' <span class="caret"></span>';
        }

        return $this->renderIcon().$label;
    }

    private function renderIcon() : string
    {
        $icon = '';
        $iconObject = $this->getVar('icon');
        if ($iconObject instanceof UI_Icon) {
            $icon = $iconObject->render() . ' ';
        }

        if (!$this->button->hasTooltip()) {
            return $icon;
        }

        $tIcon = UI::icon()
            ->setTooltip($this->button->getTooltip());

        // use a different icon if the button is disabled
        if ($this->button->isDisabled()) {
            $tIcon->warning();
        } else {
            $tIcon->help();
        }

        return
        '<span class="button-tooltip-icon">' .
            $tIcon .
        '</span>' .
        $icon;
    }

    /**
     * @var UI_Page_Sidebar_Item_Button
     */
    private $button;

    /**
     * @var UI_Bootstrap_DropdownMenu|NULL
     */
    private $menu = null;

    /**
     * @var array<string,mixed>
     */
    private $attributes = array();

    /**
     * @var string
     */
    private $mode = '';

    /**
     * @var string
     */
    private $tagname = 'button';

    /**
     * @var string
     */
    private $design = 'default';

    /**
     * @var string
     */
    private $html = '';

    /**
     * @throws Application_Exception
     */
    protected function preRender() : void
    {
        $this->button = $this->getObjectVar('button', UI_Page_Sidebar_Item_Button::class);
        $this->mode = $this->getStringVar('mode');

        if ($this->button instanceof UI_Page_Sidebar_Item_DropdownButton) {
            $this->menu = $this->button->getMenu();
        }

        if($this->hasVarNonEmpty('design')) {
            $this->design = $this->getStringVar('design');
        }

        $this->addClass('btn');
        $this->addClass('btn-block');
        $this->addClass('btn-'.$this->design);

        $this->setAttribute('id', $this->button->getID());
        $this->setAttribute('name', $this->button->getName());
        $this->setAttribute('type', 'button');
    }

    private function configureTagName() : void
    {
        if ($this->tagname === 'a' && isset($this->attributes['type'])) {
            unset($this->attributes['type']);
        }
    }

    private function configureDisabled() : void
    {
        if(!$this->button->isDisabled()) {
            return;
        }

        $this->addClass('disabled');
        $this->mode = 'none';
    }

    private function configureAction() : void
    {
        if (isset($this->menu)) {
            return;
        }

        $this->setAttribute('onclick', $this->getVar('onclick'));

        if ($this->hasVar('loadingText')) {
            $this->setAttribute('data-loading-text', $this->getStringVar('loadingText'));
        }

        $confirmMessage = $this->getVar('confirmMessage');
        if($confirmMessage instanceof UI_ClientConfirmable_Message) {
            $this->mode = 'clickable';
            $this->setVar('javascript', $confirmMessage->getJavaScript());
        }
    }

    private function configureLocking() : void
    {
        if (!$this->button->isLocked()) {
            return;
        }

        $this->button->disable();
        $this->mode = 'clickable';

        $this->addClass('btn-locked');
        $this->setAttribute('title', $this->button->getLockReason());
        $this->setVar(
            'javascript',
            'LockManager.DialogActionDisabled()'
        );
    }

    private function configureMode() : void
    {
        switch ($this->mode)
        {
            case UI_Page_Sidebar_Item_Button::MODE_LINKED:
                $this->tagname = 'a';
                $this->setAttribute('href', $this->getStringVar('url'));

                if ($this->hasVarNonEmpty('urlTarget')) {
                    $this->setAttribute('target', $this->getStringVar('urlTarget'));
                }
                break;

            case UI_Page_Sidebar_Item_Button::MODE_SUBMIT:
                $this->setAttribute('type', 'submit');
                $this->setAttribute('value', $this->attributes['name']);
                break;

            case UI_Page_Sidebar_Item_Button::MODE_CLICKABLE:
                $this->setAttribute('onclick', $this->getVar('javascript'));
                break;

            case UI_Page_Sidebar_Item_DropdownButton::MODE_DROPDOWN_MENU:
                $this->ui->addJavascriptOnload(sprintf("$('#%s').dropdown()", $this->button->getID()));
                $this->setAttribute('data-toggle', 'dropdown');
                break;
        }
    }

    private function setAttribute(string $name, string $value) : template_default_sidebar_button
    {
        $this->attributes[$name] = $value;
        return $this;
    }
}
