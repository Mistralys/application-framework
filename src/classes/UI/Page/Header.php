<?php

declare(strict_types=1);

class UI_Page_Header implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const ERROR_NAVIGATION_ALREADY_EXISTS = 108901;
    public const NAVIGATION_ID_MAIN = 'main';

    private UI_Page $page;

    /**
     * @var UI_Page_Navigation[]
     */
    private array $navigations = array();

    public function __construct(UI_Page $page)
    {
        $this->page = $page;
    }

    public function getPage() : UI_Page
    {
        return $this->page;
    }

    public function addMainNavigation() : UI_Page_Navigation
    {
        return $this->addNavigation(self::NAVIGATION_ID_MAIN);
    }

    /**
     * @param string $navigationID
     * @return UI_Page_Navigation
     * @throws UI_Exception
     */
    public function addNavigation(string $navigationID) : UI_Page_Navigation
    {
        $nav = $this->page->createNavigation($navigationID);

        $this->addNavigationInstance($nav);

        return $nav;
    }

    public function addNavigationInstance(UI_Page_Navigation $nav) : self
    {
        $navigationID = $nav->getID();

        if(!isset($this->navigations[$navigationID]))
        {
            $this->navigations[$navigationID] = $nav;
            return $this;
        }

        throw new UI_Exception(
            'Navigation already added.',
            sprintf(
                'The navigation [%s] has already been added, and may not be overwritten.',
                $navigationID
            ),
            self::ERROR_NAVIGATION_ALREADY_EXISTS
        );
    }

    /**
     * Retrieves a navigation object by its ID
     *
     * @param string $navigationID
     * @return UI_Page_Navigation|NULL
     */
    public function getNavigation(string $navigationID) : ?UI_Page_Navigation
    {
        if (array_key_exists($navigationID, $this->navigations)) {
            return $this->navigations[$navigationID];
        }

        return null;
    }

    public function renderNavigation(string $navigationID) : string
    {
        $nav = $this->getNavigation($navigationID);

        if($nav !== null)
        {
            return $nav->render();
        }

        return '';
    }

    /**
     * Renders the header using the corresponding template and
     * returns the generated HTML code.
     *
     * @return string
     * @throws UI_Themes_Exception
     */
    public function render() : string
    {
        return $this->page
            ->createTemplate('frame.header')
            ->render();
    }
}
