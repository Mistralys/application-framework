<?php

declare(strict_types=1);

class UI_Form_Renderer_CommentGenerator_DataType_Date extends UI_Form_Renderer_CommentGenerator_DataType
{
    public function addComments() : void
    {
        $date = new DateTime();
        
        $this->parts
        ->t(
            'A date in the format %1$s, with optional time in the format %2$s.',
            '<code>'.t('mm/dd/yyyy').'</code>',
            '<code>'.t('mm/dd/yyyy hour:min').'</code>'
        )
        ->t(
            'If no time is specified, %1$s is used.', 
            '<code>00:00</code>'
        )
        ->add($this->compileExamples(
            $date->format('m/d/Y'),
            $date->format('m/d/Y H:i')
        ));
    }
}
