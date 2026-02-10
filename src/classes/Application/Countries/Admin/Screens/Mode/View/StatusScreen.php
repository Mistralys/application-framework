<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Mode\View;

use Application\AppFactory;
use Application\Countries\Admin\Traits\CountryViewInterface;
use Application\Countries\Admin\Traits\CountryViewTrait;
use Application\Countries\Rights\CountryScreenRights;
use Application_Countries;
use Application_Countries_Country;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

/**
 * @property Application_Countries_Country $record
 */
class StatusScreen extends BaseRecordStatusSubmode implements CountryViewInterface
{
    use CountryViewTrait;

    public const string URL_NAME = 'status';

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

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_STATUS;
    }

    protected function createCollection(): Application_Countries
    {
        return AppFactory::createCountries();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getRecordStatusURL(): AdminURLInterface
    {
        return $this->record->adminURL()->status();
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $currency = $this->record->getCurrency();
        $locale = $this->record->getLocale();

        $grid->add(t('ISO Code'), $this->record->getISO());
        $grid->add(t('Currency'), $currency->getPlural())->setComment(sb()->mono($currency->getSymbol()));
        $grid->add(t('Locale'), $locale->getLabel())->setComment(sb()->mono($locale->getCode()));
    }
}
