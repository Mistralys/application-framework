<?php
/**
 * File containing the {@link UI_MarkupEditor_Redactor} class.
 * @package Application
 * @subpackage MarkupEditor
 * @see UI_MarkupEditor_Redactor
 */

/**
 * UI helper that handles adding the required clientside includes
 * for the redactor WYSIWYG editor.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_MarkupEditor_Redactor extends UI_MarkupEditor
{
    public static function getLabel() : string
    {
        return 'Redactor ('.t('Legacy editor, obsolete').')';
    }
    
    protected $xhtml = true;
    
    protected $allowedTags = array();

    protected $buttons = array(
        'html',
            '|', 
        'bold',
        'italic',
        'underline',
        '|',
        'orderedlist',
        'unorderedlist', 
           '|'
    );
    
    public function getDefaultOptions() : array
    {
        return array();
    }
    
    protected $plugins = array();
    
   /**
    * Adds the name of a redactor plugin to load. Note that
    * each plugin is only added once.
    * 
    * @param string $name
    * @param string $src The relative path to the javascript include file of the plugin, will be added automatically.
    * @return UI_MarkupEditor_Redactor
    */
    public function addPlugin($name, $src=null)
    {
        if(!isset($this->plugins[$name])) {
            $this->plugins[$name] = $src;
        }
        
        return $this;
    }
    
   /**
    * Sets whether to use XHTML. Default: <code>true</code>.
    * @param boolean $xhtml
    * @return UI_MarkupEditor_Redactor
    */
    public function setXHTML($xhtml=true)
    {
        $this->xhtml = true;
        return $this;
    }
    
   /**
    * Sets the HTML tags allowed in the editor. This is an
    * array with lowercase tag names.
    * 
    * @param array $tags
    * @return UI_MarkupEditor_Redactor
    */
    public function setAllowedTags($tags)
    {
        $this->allowedTags = $tags;
        return $this;
    }
    
   /**
    * Sets the buttons to use in the editor control.
    * Note: only used in conjunction with the 
    * {@link configure()} method.
    * 
    * @param array $buttons
    * @return UI_MarkupEditor_Redactor
    */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
        return $this;
    }
    
   /**
    * Adds an onload configuration statement for the redactor, using 
    * the specified jquery element selector, e.g. <code>.redactor</code>.
    * 
    * @param string $selector
    * @return UI_MarkupEditor_Redactor
    */
    public function configure($selector)
    {
        $this->selector = $selector;
        return $this;
    }
    
    protected function injectJS()
    {
        $prio = 6900;
        
        $this->ui->addJavascript('markup-editor/redactor/redactor.js', $prio--);
        $this->ui->addJavascript('markup-editor/redactor/redactor.link.js', $prio--);
        $this->ui->addStylesheet('redactor.css', 'all', $prio--);
        $this->ui->addStylesheet('redactor.link.css', 'all', $prio--);
    }
    
    protected function _start()
    {
        $options = $this->options;
        $options['buttons'] = $this->buttons;
        $options['xhtml'] = $this->xhtml;
        
        if(!empty($this->plugins)) {
            $options['plugins'] = array_keys($this->plugins);
            foreach($this->plugins as $src) {
                if(!empty($src)) {
                    $this->ui->addJavascript($src);
                }
            }
        }
        
        if(!empty($this->allowedTags)) {
            $options['allowedTags'] = $this->allowedTags;
        }
        
        $this->ui->addJavascriptOnload(sprintf(
            "$('%s').redactor(%s);",
            $this->selector,
            json_encode($options)
        ));
        
        return $this;
    }
    
    public function injectControlMarkup(UI_Form_Renderer_Element $renderer, string $markup) : string
    {
        // we may be running in CLI mode
        if(!isset($_SERVER['HTTP_USER_AGENT'])) {
            return $markup;
        }
        
        // add a compatibility message for browsers that say they are Chrome
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'CriOS') !== false) 
        {
            $el = $renderer->getFormElement();
            $comment = $el->getComment();
            
            $comment .=
            '<div>&#160;</div>'.
            UI::getInstance()->createMessage(
                '<b>'.t('Note:').' '.
                t('The WYSIWYG editor is known to have issues with your browser.').'</b> '.
                t('We recommend using Firefox instead.')
            )
            ->makeWarning()
            ->makeNotDismissable()
            ->makeSlimLayout();
            
            $el->setComment($comment);
        }
        
        return $markup;
    }
}
