<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\RequestTypes\APIClientRequestInterface;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Clients\Keys\APIKeyRecordSettings;
use Application\API\Clients\Keys\APIKeysCollection;
use DBHelper\Admin\Screens\Action\BaseRecordCreateAction;
use DBHelper_BaseRecord;
use UI;
use UI\AdminURLs\AdminURLInterface;

class BaseCreateAPIKeyAction extends BaseRecordCreateAction implements APIClientRequestInterface
{
    use APIClientRequestTrait;

    public const string URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Create an API Key');
    }

    public function createCollection() : APIKeysCollection
    {
        return $this->getAPIClientRequest()->getRecordOrRedirect()->createAPIKeys();
    }

    public function getSettingsManager() : APIKeyRecordSettings
    {
        return new APIKeyRecordSettings($this, $this->getAPIClientRequest()->getRecordOrRedirect());
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The API Key %1$s has been created successfully at %2$s.',
            sb()->reference($record->getLabel()),
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURLs()->list();
    }

    protected function resolveTitle(): string
    {
        return '';
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getSubtitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->add());
    }
}
