<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\AppFactory;
use Application\Interfaces\FilterCriteriaInterface;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeTrackerCollection;
use Application_FilterSettings;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use Closure;
use DBHelper\Admin\BaseCollectionListBuilder;
use DBHelper_BaseCollection;
use UI;
use UI_DataGrid;

class TimeListBuilder extends BaseCollectionListBuilder
{
    public const COL_START_TIME = 'start_time';
    public const COL_END_TIME = 'end_time';
    public const COL_DURATION = 'duration';
    public const COL_TYPE = 'type';
    public const COL_TICKET = 'ticket';
    public const COL_ID = 'id';
    public const COL_COMMENTS = 'comments';
    public const COL_DATE = 'date';

    /**
     * @return TimeTrackerCollection
     */
    public function getCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createTimeTracker();
    }

    protected function configureFilters(FilterCriteriaInterface $filterCriteria): void
    {
    }

    protected function configureFilterSettings(Application_FilterSettings $filterSettings): void
    {
    }

    protected function configureColumns(UI_DataGrid $grid): void
    {
        $grid->addColumn(self::COL_DATE, t('Date'));

        $grid->addColumn(self::COL_START_TIME, t('Start'))
            ->setCompact()
            ->setNowrap();

        $grid->addColumn(self::COL_END_TIME, t('End'))
            ->setCompact()
            ->setNowrap();

        $grid->addColumn(self::COL_DURATION, t('Duration'));
        $grid->addColumn(self::COL_TYPE, t('Type'));
        $grid->addColumn(self::COL_TICKET, t('Ticket'));
        $grid->addColumn(self::COL_COMMENTS, t('Comments'));
    }

    protected function configureActions(UI_DataGrid $grid): void
    {
        $grid->addAction('delete', t('Delete').'...')
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
            ->makeConfirm(t('Do you really want to delete the selected entries?'))
            ->setCallback(Closure::fromCallable(array($this, 'deleteEntries')));
    }

    private function deleteEntries() : void
    {

    }

    protected function preRender(): void
    {
    }

    protected function collectEntry(object $record): array
    {
        $timeEntry = ClassHelper::requireObjectInstanceOf(
            TimeEntry::class,
            $record
        );

        return array(
            self::COL_ID => $timeEntry->getID(),
            self::COL_DATE => sb()->link(ConvertHelper::date2listLabel($timeEntry->getDate(), false, true), $timeEntry->adminURL()->status()),
            self::COL_DURATION => $timeEntry->getDuration()->getNormalized(),
            self::COL_START_TIME => $timeEntry->getStartTime()->toReadable(),
            self::COL_END_TIME => $timeEntry->getEndTime()->toReadable(),
            self::COL_TYPE => $timeEntry->getType()->getLabel(),
            self::COL_TICKET => $timeEntry->renderTicket(),
            self::COL_COMMENTS => $timeEntry->renderComments()
        );
    }

    public function getFullViewTitle(): string
    {
        return t('Available time entries');
    }

    public function getEmptyMessage(): string
    {
        return t('No time entries logged yet.');
    }

    public function getRecordTypeLabelPlural(): string
    {
        return t('Time entries');
    }

    public function getPrimaryColumnName(): string
    {
        return self::COL_ID;
    }
}
