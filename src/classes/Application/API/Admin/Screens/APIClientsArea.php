<?php
/**
 * @package API
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\API\Admin\Screens;

use Application\Admin\BaseArea;
use Application\API\Admin\APIScreenRights;
use Application\API\Admin\Screens\Mode\ClientsListMode;
use Application\API\APIManager;
use UI;
use UI_Icon;

/**
 * Abstract base class for the API Clients area.
 *
 * @package API
 * @subpackage Admin
 */
class APIClientsArea extends BaseArea
{
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
        return ClientsListMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ClientsListMode::class;
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
    }

    protected function _handleQuickNavigation(): void
    {
        $this->quickNav->addURL(
            t('API Documentation Overview'),
            APIManager::getInstance()->adminURL()->documentationOverview()
        );
    }
}
