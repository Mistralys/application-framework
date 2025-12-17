<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Mode;

use Application\AppFactory;
use Application\Countries\Admin\Traits\CountryModeInterface;
use Application\Countries\Admin\Traits\CountryModeTrait;
use Application\Countries\Rights\CountryScreenRights;
use Application_Countries_Country;
use Application_Countries_FilterCriteria;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;
use UI;
use UI_DataGrid_Entry;

/**
 * @property Application_Countries_FilterCriteria $filters
 */
class ListScreen extends BaseRecordListMode implements CountryModeInterface
{
    use CountryModeTrait;

    public const string URL_NAME = 'list';
    public const string COL_LABEL = 'label';
    public const string COL_ISO = 'iso';
    public const string COL_CURRENCY = 'currency';
    public const string COL_LANGUAGE = 'language';
    public const string COL_LOCALE_CODE = 'locale_code';

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

    protected function getEntryData(DBHelperRecordInterface $record, DBHelper_BaseFilterCriteria_Record $entry) : UI_DataGrid_Entry
    {
        $country = ClassHelper::requireObjectInstanceOf(
            Application_Countries_Country::class,
            $record
        );

        $item = $this->grid->createEntry();

        $item->setColumnValue(self::COL_LABEL, $country->getIconLabel(true));
        $item->setColumnValue(self::COL_ISO, sb()->code($country->getISO()));

        $currency = $country->getCurrency();
        $item->setColumnValue(self::COL_CURRENCY, $currency->getPlural());

        $lang = $country->getLanguage();
        $item->setColumnValue(self::COL_LANGUAGE, $lang->getLabel());
        $item->setColumnValue(self::COL_LOCALE_CODE, sb()->code($country->getLocale()->getCode()));

        return $item;
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_ISO, t('ISO'))
            ->setSortingString()
            ->setCompact()
            ->setNowrap();

        $this->grid->addColumn(self::COL_LABEL, t('Label'))
            ->setSortingString();

        $this->grid->addColumn(self::COL_CURRENCY, t('Currency'));
        $this->grid->addColumn(self::COL_LANGUAGE, t('Official language'));
        $this->grid->addColumn(self::COL_LOCALE_CODE, t('Locale'));
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-country', t('Create country...'))
            ->setIcon(UI::icon()->add())
            ->link(AppFactory::createCountries()->adminURL()->create());

        $this->sidebar->addSeparator();

        parent::_handleSidebar();
    }

    protected function configureFilters(): void
    {
        $this->filters->excludeInvariant();
    }

    protected function configureActions(): void
    {
    }

    public function getBackOrCancelURL(): string
    {
        return APP_URL;
    }
}
