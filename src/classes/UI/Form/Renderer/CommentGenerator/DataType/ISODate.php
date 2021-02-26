<?php

declare(strict_types=1);

class UI_Form_Renderer_CommentGenerator_DataType_ISODate extends UI_Form_Renderer_CommentGenerator_DataType
{
    public function addComments() : void
    {
        $date = new DateTime();
        
        $this->parts->t(
            'A date in the format %1$s.',
            sb()->code(t('yyyy-mm-dd'))
        );
        
        $this->parts->add(
            $this->compileExamples(
                $date->format('Y-m-d')
            )
        );
    }
}
