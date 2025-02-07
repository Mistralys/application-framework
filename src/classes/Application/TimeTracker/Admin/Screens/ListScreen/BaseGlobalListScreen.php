<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\ListScreen;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeListBuilder;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use UI;
use UI\DataGrid\ListBuilder\ListBuilderScreenInterface;
use UI\DataGrid\ListBuilder\ListBuilderScreenTrait;
use UI\Interfaces\ListBuilderInterface;

class BaseGlobalListScreen extends Application_Admin_Area_Mode_Submode implements ListBuilderScreenInterface
{
    use AllowableMigrationTrait;
    use ListBuilderScreenTrait;

    public const URL_NAME = 'global';
    public const LIST_ID = 'time-entries-global';

    private TimeListBuilder $grid;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available time entries');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_ENTRIES_LIST;
    }

    public function getListID() : string
    {
        return self::LIST_ID;
    }

    public function createListBuilder(): ListBuilderInterface
    {
        return new TimeListBuilder($this);
    }

    protected function _handleCustomActions(): void
    {
    }

    protected function _handleSidebarTop(): void
    {
        $this->sidebar->addButton('create', t('Create new entry').'...')
            ->setIcon(UI::icon()->add())
            ->link(AppFactory::createTimeTracker()->adminURL()->create());

        $this->sidebar->addSeparator();
    }

    protected function _handleSidebarBottom(): void
    {
    }
}
