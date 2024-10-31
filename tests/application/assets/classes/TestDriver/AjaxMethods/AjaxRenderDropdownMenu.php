<?php

declare(strict_types=1);

namespace TestDriver\AjaxMethods;

use Application_AjaxMethod;
use UI;

class AjaxRenderDropdownMenu extends Application_AjaxMethod
{
    public const METHOD_NAME = 'RenderDropdownMenu';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    protected function init()
    {
        $this->setReturnFormatHTML();
    }

    public function processHTML() : void
    {
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

        $this->sendHTMLResponse($menu->renderMenuItems());
    }
}
