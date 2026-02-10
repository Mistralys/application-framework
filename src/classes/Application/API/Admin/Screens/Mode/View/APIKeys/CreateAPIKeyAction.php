<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionInterface;
use Application\API\Admin\Traits\APIKeyActionTrait;
use Application\API\Clients\Keys\APIKeyRecordSettings;
use DBHelper\Admin\Screens\Action\BaseRecordCreateAction;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI;
use UI\AdminURLs\AdminURLInterface;

class CreateAPIKeyAction extends BaseRecordCreateAction implements APIKeyActionInterface
{
    use APIClientRequestTrait;
    use APIKeyActionTrait;

    public const string URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Create new key');
    }

    public function getTitle(): string
    {
        return t('Create an API Key');
    }

    public function getSettingsManager() : APIKeyRecordSettings
    {
        return new APIKeyRecordSettings($this, $this->getAPIClientRequest()->getRecordOrRedirect());
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
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
