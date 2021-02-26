<?php

declare(strict_types=1);

class UI_Form_Renderer_CommentGenerator_DataType_RegexHint extends UI_Form_Renderer_CommentGenerator_DataType
{
    public function addComments() : void
    {
        $this->parts->add(UI_Form::getRegexHint($this->renderDef->getDataType()));
    }
}
