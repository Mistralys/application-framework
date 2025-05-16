<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens;

use Application\AppFactory;
use Application\Countries\Admin\Screens\View\BaseStatusScreen;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_CollectionRecord;
use Application_Countries;

abstract class BaseViewScreen extends Application_Admin_Area_Mode_CollectionRecord
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'view';

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

    public function getRecordMissingURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function getDefaultSubmode(): string
    {
        return BaseStatusScreen::URL_NAME;
    }
}
