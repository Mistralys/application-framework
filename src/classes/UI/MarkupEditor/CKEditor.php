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
    public const BUTTON_BOLD = 'bold';
    public const BUTTON_ITALIC = 'italic';
    public const BUTTON_SUPERSCRIPT = 'superscript';
    public const BUTTON_LINK = 'link';
    public const BUTTON_BULLETED_LIST = 'bulletedList';
    public const BUTTON_NUMBERED_LIST = 'numberedList';
    public const BUTTON_REMOVE_FORMAT = 'removeFormat';
    public const BUTTON_UNDO = 'undo';
    public const BUTTON_REDO = 'redo';
    public const BUTTON_PASTE_AS_PLAIN_TEXT = 'pasteAsPlainText';
    public const BUTTON_STRIKETHROUGH = 'strikethrough';
    public const BUTTON_ALIGN_LEFT = 'alignment:left';
    public const BUTTON_ALIGN_CENTER = 'alignment:center';
    public const BUTTON_ALIGN_RIGHT = 'alignment:right';
    public const BUTTON_ALIGN = 'alignment';
    private bool $useCustomJSBuild = false;

    public static function getLabel() : string
    {
        return t('CKEditor5');
    }

    protected array $buttons = array(
        '|',
        self::BUTTON_BOLD,
        self::BUTTON_ITALIC,
        self::BUTTON_STRIKETHROUGH,
        self::BUTTON_SUPERSCRIPT,
        '|',
        self::BUTTON_LINK,
        '|',
        self::BUTTON_BULLETED_LIST,
        self::BUTTON_NUMBERED_LIST,
        '|',
        self::BUTTON_REMOVE_FORMAT,
        self::BUTTON_PASTE_AS_PLAIN_TEXT,
        '|',
        self::BUTTON_UNDO,
        self::BUTTON_REDO
    );
    
    protected array $plugins = array();
    
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

    /**
     * Toggles loading the default CKEditor build. If turned off,
     * it is possible to load a custom build instead (must load the
     * javascript file manually using the UI methods).
     *
     * @param bool $useCustom
     * @return $this
     */
    public function setUseCustomJSBuild(bool $useCustom=true) : self
    {
        $this->useCustomJSBuild = $useCustom;
        return $this;
    }

    protected function injectJS() : void
    {
        $prio = 6900;

        if(!$this->useCustomJSBuild) {
            $this->ui->addVendorJavascript('mistralys/appframework-ckeditor5', 'build/ckeditor.js', $prio--);
        }

        $this->ui->addStylesheet('markup-editor/ckeditor/styles.css');
    }

    protected function _start() : self
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
