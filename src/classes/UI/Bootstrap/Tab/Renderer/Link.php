<?php

declare(strict_types=1);

class UI_Bootstrap_Tab_Renderer_Link extends UI_Bootstrap_Tab_Renderer
{
    protected function _render() : string
    {
        $attributes = array(
            'href' => $this->tab->getURL(),
            'id', $this->tab->getLinkID()
        );

        $target = $this->tab->getURLTarget();
        if(!empty($target)) {
            $attributes['target'] = $target;
        }
        
        return
        '<a '.compileAttributes($attributes).'>'.
            $this->renderLabel().
        '</a>';
    }
}
