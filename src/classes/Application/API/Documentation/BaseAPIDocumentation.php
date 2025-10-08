<?php

declare(strict_types=1);

namespace Application\API\Documentation;

use UI_Page;
use UI_Page_Template;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

abstract class BaseAPIDocumentation implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    protected UI_Page $page;

    public function __construct()
    {
        $this->page = $this->getUI()->createPage('api-documentation');

        $this->init();
    }

    abstract protected function init() : void;

    abstract protected function getContentTemplate() : UI_Page_Template;

    public function render(): string
    {
        /*
        $this->getUI()
            ->addBootstrap()
            ->addFontAwesome()
            ->addJquery()
            ->addJqueryUI();*/

        return $this->getContentTemplate()->render();
    }
}