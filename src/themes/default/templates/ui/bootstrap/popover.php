<?php
/**
 * File containing the template class {@see template_default_ui_bootstrap_popover}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_ui_bootstrap_popover
 */

declare(strict_types=1);

/**
 * Template for the javascript popovers.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Bootstrap_Popover
 */
class template_default_ui_bootstrap_popover extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $this->ui->addStylesheet('ui/popover.css');

        $this->ui->addJavascriptOnload(sprintf(
            "$('#%s').popover(%s)",
            $this->elementID,
            json_encode($this->popoverOptions)
        ));
    }

    /**
     * @var string
     */
    private $elementID = '';

    /**
     * @var array<string,mixed>
     */
    private $popoverOptions = array(
        'trigger' => 'manual',
        'container' => 'body',
        'html' => true
    );

    protected function preRender() : void
    {
        $this->elementID = $this->getStringVar(UI_Bootstrap_Popover::TEMPLATE_KEY_ATTACH_TO_ID);

        $this->popoverOptions['placement'] = $this->getStringVar(UI_Bootstrap_Popover::TEMPLATE_KEY_PLACEMENT);
        $this->popoverOptions['content'] = $this->getStringVar(UI_Bootstrap_Popover::TEMPLATE_KEY_CONTENT);
        $this->popoverOptions['title'] = $this->getStringVar(UI_Bootstrap_Popover::TEMPLATE_KEY_TITLE);
    }
}
