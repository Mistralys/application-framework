<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\ListScreen;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeSpans\TimeSpanCollection;
use Application\TimeTracker\TimeSpans\TimeSpanRecord;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode_CollectionList;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;

abstract class BaseTimeSpansListScreen extends Application_Admin_Area_Mode_Submode_CollectionList
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'time-spans-list';
    public const COL_TYPE = 'label';
    public const COL_DATE_START = 'dateStart';
    public const COL_DATE_END = 'dateEnd';
    public const COL_DURATION = 'duration';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Time Spans');
    }

    public function getTitle(): string
    {
        return t('Time Spans');
    }

    /**
     * @return TimeSpanCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createTimeTracker()->createTimeSpans();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        $item = ClassHelper::requireObjectInstanceOf(
            TimeSpanRecord::class,
            $record
        );

        return array(
            self::COL_TYPE => $item->getType()->getLabel(),
            self::COL_DATE_START => ConvertHelper::date2listLabel($item->getDateStart(), false, true),
            self::COL_DATE_END => ConvertHelper::date2listLabel($item->getDateEnd(), false, true),
            self::COL_DURATION => $item->getDurationString(),
        );
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_TYPE, t('Type'));
        $this->grid->addColumn(self::COL_DATE_START, t('Start Date'));
        $this->grid->addColumn(self::COL_DATE_END, t('End Date'));
        $this->grid->addColumn(self::COL_DURATION, t('Duration'));
    }

    protected function configureActions(): void
    {

    }

    public function getBackOrCancelURL(): string
    {
        return (string)AppFactory::createTimeTracker()->adminURL()->list();
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_TIME_SPANS_LIST;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-time-span', t('Create new time span').'...')
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->createCollection()->adminURL()->create());
    }
}
