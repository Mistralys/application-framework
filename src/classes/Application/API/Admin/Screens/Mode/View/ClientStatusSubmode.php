<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\Mode\View;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Admin\Traits\ClientSubmodeInterface;
use Application\API\Admin\Traits\ClientSubmodeTrait;
use Application\API\Clients\APIClientRecord;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

class ClientStatusSubmode extends BaseRecordStatusSubmode implements ClientSubmodeInterface
{
    use ClientSubmodeTrait;
    use APIClientRequestTrait;

    public function getTitle(): string
    {
        return t('API Client Status');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_CLIENTS_VIEW_STATUS;
    }

    public function getRecordStatusURL(): AdminURLInterface
    {
        return $this->getAPIClientRequest()->requireRecord()->adminURL()->status();
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $client = ClassHelper::requireObjectInstanceOf(
            APIClientRecord::class,
            $record
        );

        $grid->add(t('Foreign ID'), $client->getForeignID());
    }
}
