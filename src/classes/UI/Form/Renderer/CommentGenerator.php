<?php

declare(strict_types=1);

use AppUtils\ClassHelper;

class UI_Form_Renderer_CommentGenerator
{
    private UI_Form_Renderer_ElementFilter_RenderDef $renderDef;
    private UI_StringBuilder $parts;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef)
    {
        $this->renderDef = $renderDef;
        $this->parts = sb();
        
        $this->addExistingComment();
        $this->addTypeComment();
        $this->addLengthComment();
        $this->addCallbackComments();
    }
    
    public function getComment() : string
    {
        return $this->parts->render();
    }
    
   /**
    * Adds any manually added comments to the stack.
    */
    private function addExistingComment() : void
    {
        $comment = trim($this->renderDef->getElementComment());
        
        if(empty($comment)) 
        {
            return;
        }
        
        // Fix missing dots
        $lastChar = mb_substr($comment, -1);
        
        if($lastChar != '.' && $lastChar != '>') 
        {
            $comment .= '.';
        }
        
        $this->parts->add($comment);
    }
    
   /**
    * Adds auto-generated, data type specific comments as applicable.
    */
    private function addTypeComment() : void
    {
        $typeClass = $this->getTypeClass();
        
        if(empty($typeClass))
        {
            return;
        }
        
        $instance = ClassHelper::requireObjectInstanceOf(
            UI_Form_Renderer_CommentGenerator_DataType::class,
            new $typeClass($this->renderDef, $this->parts)
        );
        
        $instance->addComments();
    }
    
   /**
    * Add a length hint for length-limited elements.
    * 
    * The length is specified as a string in the format
    * `from-to`, example:
    * 
    * - 1-200 
    * - 200-200 // exact length 
    * 
    * @see UI_Form::makeLengthLimited()
    */
    private function addLengthComment() : void
    {
        $length = $this->renderDef->getAttribute('data-length');
        
        if(empty($length)) 
        {
            return;
        }
        
        $tokens = explode('-', $length);
        
        if($tokens[0] == $tokens[1]) 
        {
            $this->parts->t('Exactly %1$s characters.', $tokens[0]);
        } 
        else 
        {
            $this->parts->t('%1$s to %2$s characters.', $tokens[0], $tokens[1]);
        }
    }
    
    private function addCallbackComments() : void
    {
        // elements can add a callback to generate the comments as needed.
        $callback = $this->renderDef->getElement()->getRuntimeProperty('comments-callback');
        
        if(empty($callback))
        {
            return;
        }
        
        Application::requireCallableValid($callback);
            
        $text = (string)call_user_func($callback);
            
        if(!ctype_space($text))
        {
            $this->parts->add($text);
        }
    }

    private function getTypeClass() : string
    {
        switch($this->renderDef->getDataType())
        {
            case 'iso-date':
                return UI_Form_Renderer_CommentGenerator_DataType_ISODate::class;
                
            case 'date':
                return UI_Form_Renderer_CommentGenerator_DataType_Date::class;
                
            case 'integer':
                return UI_Form_Renderer_CommentGenerator_DataType_Integer::class;
                
            case 'float':
                return UI_Form_Renderer_CommentGenerator_DataType_Float::class;
                
            case 'filename':
            case 'phone':
            case 'email':
            case 'alias':
            case 'alias_capitals':
            case 'label':
            case 'nohtml':
            case 'name_or_title':
            case 'hexcolor':
                return UI_Form_Renderer_CommentGenerator_DataType_RegexHint::class;
        }
        
        return '';
    }
}
