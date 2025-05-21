<?php
/**
 * @package TestDriver
 * @subpackage AJAX
 */

declare(strict_types=1);

namespace TestDriver\AjaxMethods;

use Application\Ajax\BaseHTMLAjaxMethod;
use UI;

/**
 * Test AJAX method that renders menu items for use in
 * an AJAX dropdown menu. See the linked example file
 * for the corresponding code.
 *
 * @package TestDriver
 * @subpackage AJAX
 * @see /src/themes/default/templates/appinterface/buttons/ajax-dropdown/code.php
 */
class AjaxRenderDropdownMenu extends BaseHTMLAjaxMethod
{
    public const METHOD_NAME = 'RenderDropdownMenu';
    public const REQUEST_PARAM_EMPTY_MENU = 'emptyMenu';
    public const REQUEST_PARAM_TRIGGER_ERROR = 'triggerError';
    private bool $emptyMenu;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    protected function renderHTML()
    {
        if($this->emptyMenu) {
            return '';
        }

        $menu = UI::getInstance()->createDropdownMenu();

        $menu->addHeader(t('Herbs'));
        $menu->addLink(t('Basil'), '#');
        $menu->addLink(t('Lavender'), '#');
        $menu->addLink(t('Mint'), '#');
        $menu->addLink(t('Rosemary'), '#');

        $menu->addSeparator();

        $menu->addHeader(t('Greek gods'));
        $menu->addLink('Aphrodite', '#');
        $menu->addLink('Apollo', '#');
        $menu->addLink('Athena', '#');
        $menu->addLink('Zeus', '#');

        return $menu->renderMenuItems();
    }

    protected function validateRequest() : void
    {
        if($this->request->getBool(self::REQUEST_PARAM_TRIGGER_ERROR)) {
            $this->sendError(t('Error triggered by request'));
        }

        $this->emptyMenu = $this->request->getBool(self::REQUEST_PARAM_EMPTY_MENU);
    }
}
