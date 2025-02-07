<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\ListScreen;

use Application\TimeTracker\Admin\TimeListBuilder;
use Application_Admin_Area_Mode_Submode;
use UI\DataGrid\ListBuilder\ListBuilderScreenInterface;
use UI\DataGrid\ListBuilder\ListBuilderScreenTrait;
use UI\Interfaces\ListBuilderInterface;

class BaseDayListScreen extends Application_Admin_Area_Mode_Submode implements ListBuilderScreenInterface
{
    use ListBuilderScreenTrait;

    public const URL_NAME = 'day';
    public const LIST_ID = 'time-entries-day';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Day view');
    }

    public function getTitle(): string
    {
        return t('Day view');
    }

    public function getListID() : string
    {
        return self::LIST_ID;
    }

    public function createListBuilder(): ListBuilderInterface
    {
        return new TimeListBuilder($this);
    }

    protected function _handleSidebarTop(): void
    {
    }

    protected function _handleSidebarBottom(): void
    {
    }


}
