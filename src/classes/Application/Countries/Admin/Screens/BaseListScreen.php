<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens;

use Application\AppFactory;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_CollectionList;
use Application_Countries_Country;
use AppUtils\ClassHelper;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI_DataGrid_Entry;

class BaseListScreen extends Application_Admin_Area_Mode_CollectionList
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'list';
    public const COL_LABEL = 'label';
    public const COL_ISO = 'iso';
    public const COL_CURRENCY = 'currency';
    public const COL_LANGUAGE = 'language';
    public const COL_LOCALE_CODE = 'locale_code';
    public const COL_CURRENCY_SYMBOL = 'currency_symbol';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_LIST;
    }

    public function getNavigationTitle(): string
    {
        return t('List');
    }

    public function getTitle(): string
    {
        return t('Available countries');
    }

    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createCountries();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : UI_DataGrid_Entry
    {
        $country = ClassHelper::requireObjectInstanceOf(
            Application_Countries_Country::class,
            $record
        );

        $item = $this->grid->createEntry(array());

        $item->setColumnValue(self::COL_LABEL, $country->getIconLabel());
        $item->setColumnValue(self::COL_ISO, sb()->code($country->getISO()));

        $currency = $country->getCurrency();
        $item->setColumnValue(self::COL_CURRENCY, $currency->getPlural());
        $item->setColumnValue(self::COL_CURRENCY_SYMBOL, sb()->mono($currency->getSymbol()));

        $lang = $country->getLanguage();
        $item->setColumnValue(self::COL_LANGUAGE, $lang->getLabel());
        $item->setColumnValue(self::COL_LOCALE_CODE, sb()->code($country->getLocale()->getCode()));

        return $item;
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_ISO, t('ISO code'))
            ->setSortable(true, Application_Countries_Country::COL_ISO)
            ->setCompact()
            ->setNowrap();

        $this->grid->addColumn(self::COL_LABEL, t('Label'))
            ->setSortable(false, Application_Countries_Country::COL_LABEL);

        $this->grid->addColumn(self::COL_CURRENCY, t('Currency'));
        $this->grid->addColumn(self::COL_CURRENCY_SYMBOL, t('Symbol'))->setCompact();
        $this->grid->addColumn(self::COL_LANGUAGE, t('Official language'));
        $this->grid->addColumn(self::COL_LOCALE_CODE, t('Locale code'));
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-country', t('Create new...'))
            ->setIcon(UI::icon()->add())
            ->link(AppFactory::createCountries()->adminURL()->create());

        $this->sidebar->addSeparator();

        parent::_handleSidebar();
    }

    protected function configureActions(): void
    {
    }

    public function getBackOrCancelURL(): string
    {
        return APP_URL;
    }
}
