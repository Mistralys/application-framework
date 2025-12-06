<?php

declare(strict_types=1);

use Application\MarkdownRenderer;
use AppUtils\ClassHelper;

class UI_Form_Renderer_CommentGenerator
{
    public const string PROPERTY_COMMENTS_CALLBACK = 'comments-callback';
    public const string PROPERTY_MARKDOWN_SUPPORT = 'markdown_support';

    private UI_Form_Renderer_ElementFilter_RenderDef $renderDef;
    private UI_StringBuilder $parts;
    
    public function __construct(UI_Form_Renderer_ElementFilter_RenderDef $renderDef)
    {
        $this->renderDef = $renderDef;
        $this->parts = sb();
        
        $this->addExistingComment();
        $this->addMarkdownComment();
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
        
        if($lastChar !== '.' && $lastChar !== '>')
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
        
        if($tokens[0] === $tokens[1])
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
        $callback = $this->getProperty(self::PROPERTY_COMMENTS_CALLBACK);
        
        if(empty($callback))
        {
            return;
        }
        
        Application::requireCallableValid($callback);
            
        $text = (string)$callback();
            
        if(!ctype_space($text))
        {
            $this->parts->add($text);
        }
    }

    private function getTypeClass() : string
    {
        return match ($this->renderDef->getDataType()) {
            'iso-date' => UI_Form_Renderer_CommentGenerator_DataType_ISODate::class,
            'date' => UI_Form_Renderer_CommentGenerator_DataType_Date::class,
            'integer' => UI_Form_Renderer_CommentGenerator_DataType_Integer::class,
            'float' => UI_Form_Renderer_CommentGenerator_DataType_Float::class,
            'filename', 'phone', 'email', 'alias', 'alias_capitals', 'label', 'nohtml', 'name_or_title', 'hexcolor' => UI_Form_Renderer_CommentGenerator_DataType_RegexHint::class,
            default => '',
        };
    }

    private function addMarkdownComment() : void
    {
        if($this->getProperty(self::PROPERTY_MARKDOWN_SUPPORT) !== true) {
            return;
        }

        $this->parts->t(
            'You may use %1$s syntax for formatting, links and more.',
            sb()->link(MarkdownRenderer::MARKDOWN_LANGUAGE_NAME, MarkdownRenderer::MARKDOWN_DOCUMENTATION_URL, true)
        );
    }

    private function getProperty(string $name) : mixed
    {
        return $this->renderDef->getElement()->getRuntimeProperty($name);
    }
}
