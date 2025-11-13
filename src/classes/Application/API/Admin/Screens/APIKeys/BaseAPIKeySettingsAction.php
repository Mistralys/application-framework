<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeyRecordSettings;
use Application\Traits\AllowableMigrationTrait;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Action\BaseRecordSettingsAction;
use DBHelper\Admin\Screens\Action\BaseRecordStatusAction;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseRecord;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

class BaseAPIKeySettingsAction extends BaseRecordSettingsAction implements APIKeyActionInterface
{
    use APIClientRequestTrait;
    use APIKeyActionTrait;

    public const string URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('API Key Settings');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_API_KEYS_SETTINGS;
    }

    protected function resolveTitle(): string
    {
        return '';
    }

    public function getFeatureRights(): array
    {
        return array(
            t('Edit API Key Settings') => APIScreenRights::SCREEN_API_KEYS_SETTINGS_EDIT
        );
    }

    public function getSettingsManager() : APIKeyRecordSettings
    {
        $record = $this->getRecord();

        return new APIKeyRecordSettings(
            $this,
            $record->getClient(),
            $record
        );
    }

    protected function getCurrentScreenURL(): AdminURLInterface
    {
        return $this->getRecord()->adminURL()->settings();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->getRecord()->getClient()->adminURL()->apiKeys();
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The settings for API key %1$s have been saved successfully at %2$s.',
            sb()->reference($record->getLabel()),
            sb()->time()
        );
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(APIScreenRights::SCREEN_API_KEYS_SETTINGS_EDIT);
    }
}
