<?php
/**
 * File containing the class {@see UI_Page_Help}.
 *
 * @package Application
 * @subpackage User Interface
 * @see UI_Page_Help
 */

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\Interfaces\StringableInterface;
use UI\Page\Help\Item\UnorderedListItem;

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
 * @see template_default_frame_page_help
 */
class UI_Page_Help extends UI_Renderable
{
    public const ERROR_CANNOT_FIND_HELPER_CLASS = 132701;
    public const ERROR_INVALID_HELPER_INSTANCE_CREATED = 132702;

    /**
    * @var string
    */
    protected string $summary = '';
    
   /**
    * @var string
    */
    protected string $template = 'frame.page-help';
    
   /**
    * @var UI_Page_Help_Item[]
    */
    protected array $items = array();

    /**
     * Adds a paragraph of text.
     *
     * @param string|number|UI_Renderable_Interface $text
     * @return UI_Page_Help_Item_Para
     * @throws UI_Exception
     */
    public function addPara($text) : UI_Page_Help_Item_Para
    {
        $item = $this->createItemByClass(UI_Page_Help_Item_Para::class, array('text' => toString($text)));
        
        $this->items[] = $item;
        
        return $item;
    }

    /**
     * Adds a subheader in the help screen.
     *
     * @param string|number|UI_Renderable_Interface $text
     * @return UI_Page_Help_Item_Header
     * @throws UI_Exception
     */
    public function addHeader($text) : UI_Page_Help_Item_Header
    {
        $item = $this->createItemByClass(UI_Page_Help_Item_Header::class, array('text' => toString($text)));
        
        $this->items[] = $item;
        
        return $item;
    }

    /**
     * @param array<int,string|int|float|StringableInterface|NULL>|string|int|float|StringableInterface|NULL ...$items
     * @return UnorderedListItem
     * @throws UI_Exception
     */
    public function addUnorderedList(...$items) : UnorderedListItem
    {
        $item = $this->createItemByClass(UnorderedListItem::class);
        $item->addItems(...$items);

        $this->items[] = $item;

        return $item;
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
        try
        {
            $class = ClassHelper::requireResolvedClass(UI_Page_Help_Item::class . '_' . $type);

            return $this->createItemByClass($class, $options);
        }
        catch (BaseClassHelperException $e)
        {
            throw new UI_Exception(
                'Cannot find help item class.',
                '',
                self::ERROR_CANNOT_FIND_HELPER_CLASS
            );
        }
    }

    /**
     * @template ClassInstanceType
     * @param class-string<ClassInstanceType> $class
     * @param array<string,mixed> $options
     * @return ClassInstanceType
     */
    protected function createItemByClass(string $class, array $options=array())
    {
        try
        {
            return ClassHelper::requireObjectInstanceOf(
                UI_Page_Help_Item::class,
                new $class($this, $options)
            );
        }
        catch (BaseClassHelperException $e)
        {
            throw new UI_Exception(
                'Invalid help item instance created.',
                '',
                self::ERROR_INVALID_HELPER_INSTANCE_CREATED
            );
        }
    }
    
    public function hasItems() : bool
    {
        return !empty($this->items);
    }
    
   /**
    * @return UI_Page_Help_Item[]
    */
    public function getItems() : array
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
