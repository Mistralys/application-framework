<?php

declare(strict_types=1);

namespace TestDriver\AjaxMethods;

use Application_AjaxMethod;
use UI;

class AjaxRenderDropdownMenu extends Application_AjaxMethod
{
    public const METHOD_NAME = 'RenderDropdownMenu';
    public const REQUEST_PARAM_EMPTY_MENU = 'emptyMenu';
    public const REQUEST_PARAM_TRIGGER_ERROR = 'triggerError';

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
        if($this->request->getBool(self::REQUEST_PARAM_EMPTY_MENU)) {
            $this->sendHTMLResponse('');
        }

        if($this->request->getBool(self::REQUEST_PARAM_TRIGGER_ERROR)) {
            $this->sendError(t('Error triggered by request'));
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

        $this->sendHTMLResponse($menu->renderMenuItems());
    }
}
