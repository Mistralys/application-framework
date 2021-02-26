<?php

class UI_Page_Header
{
    /**
     * @var UI_Page
     */
    private $page;

    private $navigations = array();

    public function __construct(UI_Page $page)
    {
        $this->page = $page;
    }

    /**
     * @param string $navigationID
     * @return UI_Page_Navigation
     */
    public function addNavigation($navigationID)
    {
        $nav = $this->page->createNavigation($navigationID);
        $this->navigations[$navigationID] = $nav;

        return $nav;
    }

    /**
     * Retrieves a navigation object by its ID
     *
     * @param string $navigationID
     * @return UI_Page_Navigation|NULL
     */
    public function getNavigation($navigationID)
    {
        if (array_key_exists($navigationID, $this->navigations)) {
            return $this->navigations[$navigationID];
        }

        return null;
    }

    /**
     * Renders the header using the corresponding template and
     * returns the generated HTML code.
     *
     * @return string
     */
    public function render()
    {
        $template = $this->page->createTemplate('frame.header');

        $out = $template->render();

        return $out;
    }

    /**
     * Displays the HTML code for the header.
     *
     * @see render()
     */
    public function display()
    {
        echo $this->render();
    }
}