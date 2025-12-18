<?php
/**
 * @package Application
 * @subpackage News Screens
 * @see \Application\NewsCentral\Admin\Screens\ManageNewsArea
 */

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens;

use Application\Admin\BaseArea;
use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Screens\Mode\NewsListMode;
use Application\Traits\AllowableMigrationTrait;
use UI;
use UI_Icon;

/**
 * Base class for the news area, where the news
 * articles can be managed.
 *
 * @package Application
 * @subpackage News Screens
 */
class ManageNewsArea extends BaseArea
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'news';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_NEWS;
    }

    public function getNavigationIcon(): UI_Icon
    {
        return UI::icon()->news();
    }

    public function getDefaultMode(): string
    {
        return NewsListMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return NewsListMode::class;
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
            ->makeLinked(AppFactory::createNews()->adminURL()->manage()->base());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->addURL(t('News entries'), AppFactory::createNews()->adminURL()->manage()->list())
            ->setIcon(UI::icon()->news());

        $this->subnav->addURL(t('News categories'), AppFactory::createNews()->createCategories()->adminURL()->list())
            ->setIcon(UI::icon()->category());
    }
}
