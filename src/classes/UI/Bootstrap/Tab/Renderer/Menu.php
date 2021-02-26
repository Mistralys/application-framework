<?php

declare(strict_types=1);

class UI_Bootstrap_Tab_Renderer_Menu extends UI_Bootstrap_Tab_Renderer
{
    protected function _render() : string
    {
        $this->tab->addClass('dropdown');

        $menu = $this->tab->getMenu();

        if(!$menu)
        {
            return '';
        }

        return
            $this->ui->createAnchor($this->tab->getLabel().' '.UI::icon()->caretDown())
                ->setAttribute('data-toggle', 'dropdown')
                ->addClass('dropdown-toggle')
                ->setHref('#')
                ->render().
            $menu->render();
    }
}
