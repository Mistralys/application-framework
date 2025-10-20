<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\Traits\APIClientRecordScreenTrait;
use Application\API\Clients\APIClientRecordSettings;
use Application\Traits\AllowableMigrationTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsScreen;
use DBHelper_BaseRecord;

abstract class BaseAPIClientSettingsScreen extends BaseRecordSettingsScreen
{
    use AllowableMigrationTrait;
    use APIClientRecordScreenTrait;

    public const string URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('API Client Settings');
    }

    public function getSettingsManager() : APIClientRecordSettings
    {
        return new APIClientRecordSettings($this, $this->getRecord());
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_CLIENTS_SETTINGS;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->can(APIScreenRights::SCREEN_CLIENTS_SETTINGS_EDIT);
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The %1$s API Client settings have been updated successfully at %2$s.',
            sb()->reference($record->getLabel()),
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->getRecord()->adminURL()->base();
    }
}
