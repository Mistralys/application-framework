<?php
/**
 * File containing the {@link UI_Page_Footer} class.
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Footer
 */

/**
 * UI rendering class used to handle the footer of the layout.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * @link http://www.mistralys.com
 */
class UI_Page_Footer
{
    /**
     * @var UI_Page
     */
    private $page;

    public function __construct(UI_Page $page)
    {
        $this->page = $page;
    }

    public function render()
    {
        $template = $this->page->createTemplate('frame.footer');

        $out = $template->render();

        return $out;
    }

    public function display()
    {
        echo $this->render();
    }
}