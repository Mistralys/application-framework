<?php

declare(strict_types=1);

class UI_Form_Renderer_CommentGenerator_DataType_Integer extends UI_Form_Renderer_CommentGenerator_DataType
{
    public function addComments() : void
    {
        $min = intval($this->renderDef->getAttribute('data-min'));
        $max = intval($this->renderDef->getAttribute('data-max'));
        
        $this->parts->add(UI_Form::getRegexHint($this->renderDef->getDataType()));
        
        if($min > 0 && $max > 0) 
        {
            $this->parts
            ->t('Possible values:')
            ->t('From %1$s to %2$s.', $min, $max);
        } 
        else if($min > 0) 
        {
            $this->parts
            ->t('Possible values:')
            ->t('%1$s minimum.', $min);
        } 
        else if($max > 0) 
        {
            $this->parts
            ->t('Possible values:')
            ->t('%1$s maximum.', $max);
        } 
        else 
        {
            $this->parts->add($this->compileExamples(
                rand(1,99),
                rand(900,9000)
            ));
        }
    }
}
