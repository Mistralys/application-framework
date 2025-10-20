<?php
/**
 * @package API
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\API\Admin\Screens;

use Application\API\Admin\APIScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area;
use UI;
use UI_Icon;

/**
 * Abstract base class for the API Clients area.
 *
 * @package API
 * @subpackage Admin
 */
abstract class BaseAPIClientsArea extends Application_Admin_Area
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'api-clients';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('API Clients');
    }

    public function getTitle(): string
    {
        return t('API Clients');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->apiClients();
    }

    public function getDefaultMode(): string
    {
        return BaseClientsListScreen::URL_NAME;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_CLIENTS_AREA;
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return false;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()->setIcon($this->getNavigationIcon());
        $this->renderer->setAbstract(sb()
            ->t('This is an overview of all API Clients that have been registered in the system.')
            ->t('It enables access to the APIs provided by the application through API keys specific to each client.')
        );
    }
}
