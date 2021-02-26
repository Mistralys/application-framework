<?php

declare(strict_types=1);

class UI_Form_Renderer_Tab
{
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef
    */
    private $renderDef;
    
   /**
    * @var string
    */
    private $content;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef, string $content)
    {
        $this->renderDef = $renderDef;
        $this->content = $content;
    }
}
