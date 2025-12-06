<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Clients\Keys\APIKeyRecord;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Action\BaseRecordStatusAction;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

class BaseAPIKeyStatusAction extends BaseRecordStatusAction implements APIKeyActionInterface
{
    use APIClientRequestTrait;
    use APIKeyActionTrait;

    public const string URL_NAME = 'status';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('API Key Status');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_API_KEYS_STATUS;
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $apiKey = ClassHelper::requireObjectInstanceOf(
            APIKeyRecord::class,
            $record
        );

        $grid->add(t('API Key'), sb()->codeCopy($apiKey->getAPIKey()));
        $grid->addDate(t('Expiry date'), $apiKey->resolveExpiryDate())->ifEmpty(t('No expiry date'));
        $grid->addDate(t('Date created'), $apiKey->getDateCreated());
        $grid->add(t('Created by'), $apiKey->getCreatedBy()->getLabel());

        $grid->addHeader(t('API methods'));

        $grid->addBoolean(t('All granted?'), $apiKey->areAllMethodsGranted())->makeYesNo();
        $grid->addAmount(t('Granted methods'), $apiKey->getMethods()->countMethods());

        $grid->addHeader(t('Usage'));

        $grid->addAmount(t('Usage count'), $apiKey->getUsageCount());
        $grid->addDate(t('Last accessed'), $apiKey->getLastUsedDate())->ifEmpty(t('Never accessed'));
    }

    protected function getCurrentScreenURL(): AdminURLInterface
    {
        return $this->getRecord()->adminURL()->status();
    }
}
