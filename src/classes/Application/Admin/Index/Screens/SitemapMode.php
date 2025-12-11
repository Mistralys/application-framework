<?php

declare(strict_types=1);

namespace Application\Admin\Index\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Development\Admin\DevScreenRights;
use Application\Themes\DefaultTemplate\Admin\SitemapTmpl;
use UI_Themes_Theme_ContentRenderer;

class SitemapMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'sitemap';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Sitemap');
    }

    public function getTitle(): string
    {
        return t('Application Sitemap');
    }

    public function getDevCategory(): string
    {
        return t('Documentation');
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_SITEMAP;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithoutSidebar()
            ->appendTemplateClass(SitemapTmpl::class);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());

        $this->renderer->setAbstract(sb()
            ->t(
                'This shows a tree of all administration screens available in the %1$s application.',
                $this->driver->getAppNameShort()
            )
            ->nl()
            ->t('There are a total of %1$d screens in the registry.', AdminScreenIndex::getInstance()->countScreens())
        );
    }
}
