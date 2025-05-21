<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\View;

use Application\AppFactory;
use Application\Countries\Rights\CountryScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_CollectionEdit;
use Application_Countries;
use Application_Countries_Country;
use DBHelper_BaseRecord;

/**
 * @property Application_Countries_Country $record
 */
abstract class BaseSettingsScreen extends Application_Admin_Area_Mode_Submode_CollectionEdit
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Country settings');
    }

    public function getRequiredRight(): string
    {
        return CountryScreenRights::SCREEN_SETTINGS;
    }

    public function getSettingsManager()
    {
        return AppFactory::createCountries()->createSettingsManager($this, $this->record);
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->canEditCountries();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function createCollection() : Application_Countries
    {
        return AppFactory::createCountries();
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The country settings have been saved sucessfully at %1$s.',
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }
}
