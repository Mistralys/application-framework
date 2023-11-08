<?php
/**
 * @package Application
 * @subpackage News Screens
 * @see \Application\Admin\Area\BaseNewsScreen
 */

declare(strict_types=1);

namespace Application\Admin\Area;

use Application\Admin\Area\News\BaseNewsListScreen;
use Application\AppFactory;
use Application_Admin_Area;
use UI;
use UI_Icon;

/**
 * Base class for the news area, where the news
 * articles can be managed.
 *
 * @package Application
 * @subpackage News Screens
 */
abstract class BaseNewsScreen extends Application_Admin_Area
{
    public const URL_NAME = 'news';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewNews();
    }

    public function getNavigationIcon(): UI_Icon
    {
        return UI::icon()->news();
    }

    public function getDefaultMode(): string
    {
        return BaseNewsListScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getNavigationTitle(): string
    {
        return t('News central');
    }

    public function getTitle(): string
    {
        return t('Application news central');
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()
            ->setIcon(UI::icon()->news());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createNews()->getAdminURL());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->addURL(t('News entries'), AppFactory::createNews()->getAdminListURL())
            ->setIcon(UI::icon()->news());

        $this->subnav->addURL(t('News categories'), AppFactory::createNews()->createCategories()->getAdminListURL())
            ->setIcon(UI::icon()->category());
    }
}
