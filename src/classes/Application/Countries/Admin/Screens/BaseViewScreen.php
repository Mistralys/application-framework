<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens;

use Application\AppFactory;
use Application\Countries\Admin\Screens\View\BaseStatusScreen;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Countries;
use Application_Countries_Country;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property Application_Countries_Country $record
 */
abstract class BaseViewScreen extends BaseRecordMode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View a country');
    }

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_VIEW;
    }

    protected function createCollection() : Application_Countries
    {
        return AppFactory::createCountries();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getDefaultSubmode(): string
    {
        return BaseStatusScreen::URL_NAME;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->record->getLabel());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->view());
    }

    protected function _handleSubnavigation(): void
    {
        $urls = $this->record->adminURL();

        $this->subnav->addURL(t('Status'), $urls->status())
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(t('Settings'), $urls->settings())
            ->setIcon(UI::icon()->settings());
    }
}
