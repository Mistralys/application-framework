<?php

declare(strict_types=1);

namespace UI\Page\Navigation;

use Application\AppFactory;
use Application\Application;
use Application_Driver;
use Application_User_Notepad;
use UI;
use UI\Page\Navigation\MetaNavigation\DeveloperMenu;
use UI\Page\Navigation\MetaNavigation\UserMenu;
use UI_Page_Navigation;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

/**
 * @see template_default_navigation_metanav
 */
class MetaNavigation implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const string META_LOOKUP = 'lookup';
    public const string META_PRINT_PAGE = 'print-page';
    public const string META_NOTEPAD = 'notepad';
    public const string META_DEVELOPER = 'developer';
    public const string META_USER = 'user';
    public const string META_NEWS = 'news';

    private UI_Page_Navigation $metaNav;
    private UI $ui;

    public function __construct(UI $ui)
    {
        $this->ui = $ui;
        $this->metaNav = $this->ui->getPage()->createNavigation('metanav');
    }

    public function getUI() : UI
    {
        return $this->ui;
    }

    public function getNavigation() : UI_Page_Navigation
    {
        return $this->metaNav;
    }

    public function configure() : void
    {
        $this->configureDeveloperMenu();

        $this->metaNav->addClickable('', Application_User_Notepad::getJSOpen())
            ->setAlias(self::META_NOTEPAD)
            ->setIcon(UI::icon()->notepad())
            ->setTooltip(UI::tooltip(Application_User_Notepad::getTooltipText())->makeBottom());

        $this->metaNav->addClickable('', 'Driver.DialogLookup()')
            ->setAlias(self::META_LOOKUP)
            ->setIcon(UI::icon()->search())
            ->setTooltip(UI::tooltip(t('Look up an item'))->makeBottom());

        $this->metaNav->addURL('', AppFactory::createNews()->adminURL()->read()->list())
            ->setAlias(self::META_NEWS)
            ->setIcon(UI::icon()->news())
            ->setTooltip(UI::tooltip(t('Latest %1$s news', Application_Driver::getInstance()->getAppNameShort()))->makeBottom());

        $this->metaNav->addClickable('', 'window.print()')
            ->setAlias(self::META_PRINT_PAGE)
            ->setIcon(UI::icon()->printer())
            ->setTooltip(UI::tooltip(t('Print this page'))->makeBottom());

        $this->configureUserMenu();
    }

    private function configureUserMenu() : void
    {
        $menu = $this->metaNav->addDropdownMenu('')
            ->noCaret()
            ->setAlias(self::META_USER)
            ->setIcon(UI::icon()->user());

        new UserMenu($menu)->configure();
    }

    private function configureDeveloperMenu() : void
    {
        if(!$this->isDeveloperMenuEnabled()) {
            return;
        }

        $dropdown = $this->metaNav->addDropdownMenu('')
            ->noCaret()
            ->setAlias(self::META_DEVELOPER)
            ->setIcon(UI::icon()->developer());

        new DeveloperMenu($dropdown)->configure();
    }

    public function isDeveloperMenuEnabled() : bool
    {
        $preset = Application::getSession()->getPresetBySession();

        if(!empty($preset)) {
            return true;
        }

        return Application::getUser()->isDeveloper();
    }

    public function render() : string
    {
        return $this->metaNav->render();
    }
}
