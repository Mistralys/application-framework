<?php

declare(strict_types=1);

class UI_Bootstrap_Tab_Renderer_Toggle extends UI_Bootstrap_Tab_Renderer
{
    protected function _render() : string
    {
        $atts = array(
            'href' => '#'.$this->tab->getID(),
            'id' => $this->tab->getLinkID(),
            'data-toggle' => 'tab'
        );

        $statement = $this->tab->getEventStatement('select');
        
        if(!empty($statement))
        {
            $this->ui->addJavascriptOnload(sprintf(
                "$('#%s').on('shown', function(e) {%s})",
                $this->tab->getLinkID(),
                $statement
            ));
        }
        
        return
        '<a '.compileAttributes($atts).'>'.
            $this->renderLabel().
        '</a>';
    }
}
