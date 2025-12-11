<?php

declare(strict_types=1);

namespace Application\Development\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Development\Admin\DevScreenRights;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property DevelArea $area
 */
class DevelOverviewMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'overview';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): ?string
    {
        return DevScreenRights::SCREEN_OVERVIEW;
    }

    public function getTitle(): string
    {
        return t('Developer tools overview');
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithoutSidebar()
            ->appendTemplate(
                $this->createTemplate('devel.overview')
                    ->setVars(array(
                        'items' => $this->area->getItems()
                    ))
            );
    }
}