<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\Traits\APIClientRecordScreenTrait;
use Application\API\Clients\APIClientRecord;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode;
use DBHelper_BaseRecord;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

class BaseAPIClientStatusScreen extends BaseRecordStatusSubmode
{
    use APIClientRecordScreenTrait;

    public function getTitle(): string
    {
        return t('API Client Status');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_CLIENTS_VIEW_STATUS;
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function getRecordStatusURL(): AdminURLInterface
    {
        return $this->getRecord()->adminURL()->status();
    }

    /**
     * @param UI_PropertiesGrid $grid
     * @param APIClientRecord $record
     * @return void
     */
    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelper_BaseRecord $record): void
    {
        $grid->add(t('Foreign ID'), $record->getForeignID());
    }
}
