<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\AppFactory;
use Application\Interfaces\FilterCriteriaInterface;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeFilterCriteria;
use Application\TimeTracker\TimeTrackerCollection;
use Application_FilterSettings;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\Microtime;
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
    public const COL_DAY = 'day';

    public const MODE_DAY = 'day';
    public const MODE_GLOBAL = 'global';
    public const DEFAULT_MODE = self::MODE_GLOBAL;


    private string $mode = self::DEFAULT_MODE;
    private ?Microtime $fixedDate = null;
    private int $totalDuration = 0;

    /**
     * @return TimeTrackerCollection
     */
    public function getCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function enableDayMode(Microtime $date) : self
    {
        $this->mode = self::MODE_DAY;
        $this->fixedDate = $date;
        return $this;
    }

    public function isDayMode() : bool
    {
        return $this->mode === self::MODE_DAY;
    }

    protected function configureFilters(FilterCriteriaInterface $filterCriteria): void
    {
        if(!$filterCriteria instanceof TimeFilterCriteria) {
            return;
        }

        if(isset($this->fixedDate)) {
            $filterCriteria->setFixedDate($this->fixedDate);
        }
    }

    protected function configureFilterSettings(Application_FilterSettings $filterSettings): void
    {
    }

    protected function configureColumns(UI_DataGrid $grid): void
    {
        if(!$this->isDayMode()) {
            $grid->addColumn(self::COL_DATE, t('Date'))->setSortable();
        }

        $grid->addColumn(self::COL_START_TIME, t('Start'))
            ->setCompact()
            ->setNowrap();

        $grid->addColumn(self::COL_END_TIME, t('End'))
            ->setCompact()
            ->setNowrap();

        $grid->addColumn(self::COL_DURATION, t('Duration'))->setSortable();
        $grid->addColumn(self::COL_TYPE, t('Type'));
        $grid->addColumn(self::COL_TICKET, t('Ticket'));
        $grid->addColumn(self::COL_COMMENTS, t('Comments'));

        $grid->addSumsRow()->makeCallback(
            self::COL_DURATION,
            Closure::fromCallable(array($this, 'sumDuration'))
        );
    }

    private function sumDuration() : string
    {
        return DurationStringInfo::fromSeconds($this->totalDuration)->getNormalized();
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

        $duration = $timeEntry->getDuration();

        $this->totalDuration += $timeEntry->getDuration()->getTotalSeconds();

        return array(
            self::COL_ID => $timeEntry->getID(),
            self::COL_DATE => sb()->link(ConvertHelper::date2listLabel($timeEntry->getDate(), false, true), $timeEntry->adminURL()->status()),
            self::COL_DURATION => $this->renderDuration($timeEntry, $duration),
            self::COL_START_TIME => $timeEntry->getStartTime()->toReadable(),
            self::COL_END_TIME => $timeEntry->getEndTime()->toReadable(),
            self::COL_TYPE => $timeEntry->getType()->getLabel(),
            self::COL_TICKET => $timeEntry->renderTicket(),
            self::COL_COMMENTS => $timeEntry->renderComments()
        );
    }

    private function renderDuration(TimeEntry $timeEntry, DurationStringInfo $duration) : string
    {
        $fractionalHour = number_format($duration->getTotalSeconds() / 3600, 2);

        $text = sb();
        if($this->isDayMode()) {
            $text->link($duration->getNormalized(), $timeEntry->adminURL()->settings());
        } else {
            $text->add($duration->getNormalized());
        }

        return (string)$text
            ->muted(' &#160; '.$fractionalHour.' h');
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
