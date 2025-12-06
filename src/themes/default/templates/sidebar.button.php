<?php
/**
 * @package UserInterface
 * @subpackage Templates
 */

declare(strict_types=1);

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;

/**
 * Main template for the frame skeleton of all pages.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_sidebar_button extends UI_Page_Template_Custom implements ClassableInterface
{
    use ClassableTrait;

    const string VAR_BUTTON = 'button';
    const string VAR_MODE = 'mode';
    const string VAR_DESIGN = 'design';

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
        if ($this->design === 'developer') {
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
    private UI_Page_Sidebar_Item_Button $button;

    private ?UI_Bootstrap_DropdownMenu $menu = null;

    /**
     * @var array<string,mixed>
     */
    private array $attributes = array();

    private string $mode = '';
    private string $tagname = 'button';
    private string $design = 'default';

    protected function preRender() : void
    {
        $this->button = $this->getObjectVar(self::VAR_BUTTON, UI_Page_Sidebar_Item_Button::class);
        $this->mode = $this->getStringVar(self::VAR_MODE);

        if ($this->button instanceof UI_Page_Sidebar_Item_DropdownButton) {
            $this->menu = $this->button->getMenu();
        }

        if($this->hasVarNonEmpty(self::VAR_DESIGN)) {
            $this->design = $this->getStringVar(self::VAR_DESIGN);
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
