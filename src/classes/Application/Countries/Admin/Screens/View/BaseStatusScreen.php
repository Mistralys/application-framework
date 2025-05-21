<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\View;

use Application\AppFactory;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_CollectionRecord;
use Application_Countries;
use Application_Countries_Country;
use DBHelper_BaseCollection;
use UI_PropertiesGrid;

/**
 * @property Application_Countries_Country $record
 */
class BaseStatusScreen extends Application_Admin_Area_Mode_Submode_CollectionRecord
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'status';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_STATUS;
    }

    /**
     * @return Application_Countries
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createCountries();
    }

    public function getRecordMissingURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    protected function _handleHelp(): void
    {

    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->record->adminURL()->status());
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendContent($this->createPropertiesGrid())
            ->makeWithoutSidebar();
    }

    protected function createPropertiesGrid() : UI_PropertiesGrid
    {
        $grid = $this->ui->createPropertiesGrid();

        $currency = $this->record->getCurrency();
        $locale = $this->record->getLocale();

        $grid->add(t('ISO Code'), $this->record->getISO());
        $grid->add(t('Currency'), $currency->getPlural())->setComment(sb()->mono($currency->getSymbol()));
        $grid->add(t('Locale'), $locale->getLabel())->setComment(sb()->mono($locale->getCode()));

        return $grid;
    }
}
