<?php
/**
 * File containing the class {@see UI_Page_Help}.
 *
 * @package Application
 * @subpackage User Interface
 * @see UI_Page_Help
 */

declare(strict_types=1);

/**
 * Handles the inline page help interface, which is used to
 * add documentation relevant to the current page. It is accessed
 * in the administration screens via the `_handleHelp()` method.
 *
 * @package Application
 * @subpackage User Interface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Screen::_handleHelp()
 * @see Application_Admin_Skeleton::$help
 * @see template_default_frame_page_help
 */
class UI_Page_Help extends UI_Renderable implements UI_Renderable_Interface
{
   /**
    * @var string
    */
    protected $summary = '';
    
   /**
    * @var string
    */
    protected $template = 'frame.page-help';
    
   /**
    * @var UI_Page_Help_Item[]
    */
    protected $items = array();
    
   /**
    * Adds a paragraph of text.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Page_Help_Item_Para
    */
    public function addPara($text) : UI_Page_Help_Item_Para
    {
        $item = $this->createItem('Para', array('text' => toString($text)));
        
        $this->items[] = $item;
        
        return ensureType(
            UI_Page_Help_Item_Para::class,
            $item
        ); 
    }
    
   /**
    * Adds a subheader in the help screen.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Page_Help_Item_Header
    */
    public function addHeader($text) : UI_Page_Help_Item_Header
    {
        $item = $this->createItem('Header', array('text' => toString($text)));
        
        $this->items[] = $item;
        
        return ensureType(
            UI_Page_Help_Item_Header::class,
            $item
        ); 
    }
    
   /**
    * Sets the summary for this help content, which can
    * be shown before the whole help is shown.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Page_Help
    */
    public function setSummary($text) : UI_Page_Help
    {
        $this->summary = toString($text);
        
        return $this;
    }
    
    protected function createItem(string $type, array $options=array()) : UI_Page_Help_Item
    {
        $class = 'UI_Page_Help_Item_'.$type;
        
        return new $class($this, $options);
    }
    
    public function hasItems() : bool
    {
        return !empty($this->items);
    }
    
   /**
    * @return UI_Page_Help_Item[]
    */
    public function getItems()
    {
        return $this->items;
    }
    
    public function hasSummary() : bool
    {
        return !empty($this->summary);
    }
    
    public function getSummary() : string
    {
        return $this->summary;
    }

    /**
     * @return string
     * @throws Application_Exception
     *
     * @see template_default_frame_page_help
     */
    protected function _render()
    {
        return $this->page->renderTemplate(
            $this->template, 
            array(
                'help' => $this
            )
        );
    }
    
    public function setTemplate(string $id) : UI_Page_Help
    {
        $this->template = $id;
        return $this;
    }
}
