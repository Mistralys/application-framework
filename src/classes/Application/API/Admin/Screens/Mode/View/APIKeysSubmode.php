<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\Mode\View;

use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Admin\Traits\ClientSubmodeInterface;
use Application\API\Admin\Traits\ClientSubmodeTrait;
use Application\API\Admin\APIScreenRights;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeysListAction;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;

class APIKeysSubmode extends BaseRecordSubmode implements ClientSubmodeInterface
{
    use ClientSubmodeTrait;
    use APIClientRequestTrait;

    public const string URL_NAME = 'api_keys';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('API keys');
    }

    public function getNavigationTitle(): string
    {
        return t('API Keys');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_API_KEYS;
    }
    
    public function getDefaultAction(): string
    {
        return APIKeysListAction::URL_NAME;
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return APIKeysListAction::class;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getAPIClientRequest()->requireRecord()->adminURL()->apiKeys());
    }
}
