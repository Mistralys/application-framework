<?php

declare(strict_types=1);

abstract class UI_Form_Renderer_CommentGenerator_DataType
{
   /**
    * @var UI_Form_Renderer_ElementFilter_RenderDef
    */
    protected $renderDef;
    
   /**
    * @var UI_StringBuilder
    */
    protected $parts;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef, UI_StringBuilder $parts)
    {
        $this->renderDef = $renderDef;
        $this->parts = $parts;
    }
    
    protected function compileExamples() : string
    {
        $args = func_get_args();
        
        return $this->renderDef->getRenderer()->getForm()->compileExamples(...$args);
    }
    
    abstract public function addComments() : void;
}
