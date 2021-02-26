<?php

declare(strict_types=1);

class UI_Form_Renderer_CommentGenerator_DataType_Float extends UI_Form_Renderer_CommentGenerator_DataType
{
    public function addComments() : void
    {
        $this->parts
        ->add(UI_Form::getRegexHint($this->renderDef->getDataType()))
        ->note()
        ->t('Commas are converted to dots.')
        ->add($this->compileExamples(
            rand(1,99),
            rand(1,9000).'.'.sprintf('%02d', rand(0, 99)),
            rand(1,99).'.'.sprintf('%04d', rand(0, 9000))
        ));
    }
}
