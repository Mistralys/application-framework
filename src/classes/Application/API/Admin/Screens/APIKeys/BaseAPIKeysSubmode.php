<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\Traits\APIClientRecordScreenTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;

abstract class BaseAPIKeysSubmode extends BaseRecordSubmode
{
    use APIClientRecordScreenTrait;

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
        return BaseAPIKeysListAction::URL_NAME;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getRecord()->adminURL()->apiKeys());
    }
}
