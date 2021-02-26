<?php
/**
 * File containing the {@link UI_MarkupEditor_Redactor} class.
 * @package Application
 * @subpackage MarkupEditor
 * @see UI_MarkupEditor_Redactor
 */

declare(strict_types=1);

use AppLocalize\Localization;

/**
 * Handles integrating the CKEditor WYSIWYG editor.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * 
 * @see template:ui/markup-editor/ckeditor/command
 */
class UI_MarkupEditor_CKEditor extends UI_MarkupEditor
{
    public static function getLabel() : string
    {
        return t('CKEditor5');
    }
    
    protected $buttons = array(
        '|',
        'bold',
        'italic',
        'superscript',
        '|',
        'link',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'pastePlainText',
        'removeFormat',
        '|',
        'undo',
        'redo'
    );
    
    protected $plugins = array(
        'Bold',
        'Essentials',
        'Italic',
        'List',
        'Paragraph',
        'RemoveFormat',
        'PastePlainText',
        'Superscript',
        'TextTransformation',
        'Link'
    );
    
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    public function insertButtonAfter(string $buttonName, string $afterName) : UI_MarkupEditor_CKEditor
    {
        $keep = array();
        
        foreach($this->buttons as $name)
        {
            $keep[] = $name;
            
            if($name === $afterName)
            {
                $keep[] = $buttonName;
            }
        }
        
        $this->buttons = $keep;
        
        return $this;
    }
    
    public function insertButtonBefore(string $buttonName, string $beforeName) : UI_MarkupEditor_CKEditor
    {
        $keep = array();
        
        foreach($this->buttons as $name)
        {
            if($name === $beforeName)
            {
                $keep[] = $buttonName;
            }

            $keep[] = $name;
        }
        
        $this->buttons = $keep;
        
        return $this;
    }
    
    protected function injectJS()
    {
        $prio = 6900;
        
        $this->ui->addJavascript('markup-editor/ckeditor/build/ckeditor.js', $prio--);
        $this->ui->addStylesheet('markup-editor/ckeditor/styles.css');
    }
    
    protected function _start()
    {
        $js = $this->ui->createTemplate('ui/markup-editor/ckeditor/command')
        ->setVar('selector', $this->selector)
        ->setVar('buttons', $this->buttons)
        ->setVar('language-ui', Localization::getAppLocale()->getLanguageCode())
        ->setVar('language-content', $this->country->getLanguageCode())
        ->setVar('plugin-names', $this->plugins)
        ->render();
        
        $this->ui->addJavascriptOnload($js);
        
        return $this;
    }
    
   /**
    * Adds a plugin to use. 
    * 
    * NOTE: The CKEditor build must already include the target
    * plugin. The editor's javascript include is one giant file
    * that contains everything. If a plugin is not included yet,
    * it has to be added to the build first.
    * 
    * @param string $name
    * @return UI_MarkupEditor_CKEditor
    */
    public function addPlugin(string $name) : UI_MarkupEditor_CKEditor
    {
        if(!in_array($name, $this->plugins)) 
        {
            $this->plugins[] = $name;
        }
        
        return $this;
    }
    
    public function injectControlMarkup(UI_Form_Renderer_Element $renderer, string $markup) : string
    {
        // we may be running in CLI mode
        if(!isset($_SERVER['HTTP_USER_AGENT'])) {
            return $markup;
        }
        
        return $markup;
    }
}
