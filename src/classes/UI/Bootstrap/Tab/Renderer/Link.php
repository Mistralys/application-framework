<?php

declare(strict_types=1);

class UI_Bootstrap_Tab_Renderer_Link extends UI_Bootstrap_Tab_Renderer
{
    protected function _render() : string
    {
        $atts = array(
            'href' => $this->tab->getURL(),
            'id', $this->tab->getLinkID()
        );
        
        return
        '<a '.compileAttributes($atts).'>'.
            $this->renderLabel().
        '</a>';
    }
}
