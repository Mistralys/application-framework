<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\View;

use Application\AppFactory;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_Action_CollectionRecord;
use Application_Countries;
use DBHelper_BaseCollection;

class BaseStatusScreen extends Application_Admin_Area_Mode_Submode_Action_CollectionRecord
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

    protected function _renderContent()
    {
        return $this->renderer
            ->makeWithoutSidebar();
    }
}
