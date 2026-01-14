<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\AppFactory;
use Application\Interfaces\FilterCriteriaInterface;
use Application\TimeTracker\Admin\ListBuilder\TicketSummaryRenderer;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\DayListSubmode;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeFilterCriteria;
use Application\TimeTracker\TimeTrackerCollection;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\Microtime;
use DBHelper\Admin\BaseCollectionListBuilder;
use Application\FilterSettings\FilterSettingsInterface;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_DataGrid_Entry;
use UI_DataGrid_RedirectMessage;

class TimeListBuilder extends BaseCollectionListBuilder
{
    public const string COL_START_TIME = 'start_time';
    public const string COL_END_TIME = 'end_time';
    public const string COL_DURATION = 'duration';
    public const string COL_TYPE = 'type';
    public const string COL_TICKET = 'ticket';
    public const string COL_ID = 'id';
    public const string COL_PROCESSED = 'processed';
    public const string COL_COMMENTS = 'comments';
    public const string COL_DATE = 'date';

    public const string MODE_DAY = 'day';
    public const string MODE_GLOBAL = 'global';
    public const string DEFAULT_MODE = self::MODE_GLOBAL;


    private string $mode = self::DEFAULT_MODE;
    private ?Microtime $fixedDate = null;
    private int $totalDuration = 0;
    private bool $summaryEnabled = false;

    public function getCollection(): TimeTrackerCollection
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

    /**
     * Enables the summary table below the list with a
     * tally of total durations per task ticket.
     *
     * @param bool $enabled
     * @return $this
     */
    public function enableSummary(bool $enabled=true) : self
    {
        $this->summaryEnabled = $enabled;
        return $this;
    }

    public function isSummaryEnabled() : bool
    {
        return $this->summaryEnabled;
    }

    /**
     * Renders a summary table below the list that shows the total
     * duration per same ticket.
     *
     * > **NOTE**: Must be called after the list has been rendered,
     * > as it depends on the entries that have been collected during
     * > the rendering process.
     *
     * @return string
     */
    public function renderTicketSummary() : string
    {
        if(empty($this->summaryEntries) || !$this->isSummaryEnabled()) {
            return '';
        }

        $renderer = new TicketSummaryRenderer($this->summaryEntries)
            ->setGridID($this->getGridID().'_summary');

        $this->summaryEntries = array();

        return (string)$renderer;
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

    protected function configureFilterSettings(FilterSettingsInterface $filterSettings): void
    {
    }

    protected function configureColumns(UI_DataGrid $grid): void
    {
        if(!$this->isDayMode()) {
            $grid->addColumn(self::COL_DATE, t('Date'))->setSortable();
        }

        $grid->addColumn(self::COL_START_TIME, t('Start'))
            ->setSortable(false, TimeTrackerCollection::COL_TIME_START)
            ->setCompact()
            ->setNowrap();

        $grid->addColumn(self::COL_END_TIME, t('End'))
            ->setSortable(false, TimeTrackerCollection::COL_TIME_END)
            ->setCompact()
            ->setNowrap();

        $grid->addColumn(self::COL_DURATION, t('Duration'))->setSortable();
        $grid->addColumn(self::COL_TYPE, t('Type'));
        $grid->addColumn(self::COL_TICKET, t('Ticket'));
        $grid->addColumn(self::COL_PROCESSED, t('Processed?'))->setCompact();
        $grid->addColumn(self::COL_COMMENTS, t('Comments'));

        $grid->addSumsRow()->makeCallback(
            self::COL_DURATION,
            $this->sumDuration(...)
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
            ->setCallback($this->deleteEntries(...));

        $grid->addSeparatorAction();

        $grid->addAction('set_processed', t('Set processed'))
            ->setIcon(UI::icon()->yes())
            ->setCallback($this->setEntriesProcessed(...));

        $grid->addAction('set_unprocessed', t('Set not processed'))
            ->setIcon(UI::icon()->no())
            ->setCallback($this->setEntriesNotProcessed(...));
    }

    private function deleteEntries(UI_DataGrid_Action $action) : void
    {
        $action->createRedirectMessage($this->resolveRedirectURL())
            ->single(t('The time entry %1$s has been deleted successfully at %2$s.', sb()->bold('$label'), '$time'))
            ->multiple(t('%1$s time entries have been deleted successfully at %2$s.', sb()->bold('$amount'), '$time'))
            ->none(t('No time entries selected that could be deleted.'))
            ->processDeleteDBRecords(AppFactory::createTimeTracker())
            ->redirect();
    }

    private function setEntriesProcessed(UI_DataGrid_Action $action) : void
    {
        $redirect = $action->createRedirectMessage($this->resolveRedirectURL())
            ->single(t('The time entry %1$s has been successfully marked as processed at %2$s.', sb()->bold('$label'), '$time'))
            ->multiple(t('%1$s time entries have been successfully marked as processed at %2$s.', sb()->bold('$amount'), '$time'))
            ->none(t('No time entries selected that could be marked as processed.'));

        $this->setProcessedFlag($redirect, $action->getSelectedValues(), true);

        $redirect->redirect();
    }

    private function setEntriesNotProcessed(UI_DataGrid_Action $action) : void
    {
        $redirect = $action->createRedirectMessage($this->resolveRedirectURL())
            ->single(t('The time entry %1$s has been successfully marked as not processed at %2$s.', sb()->bold('$label'), '$time'))
            ->multiple(t('%1$s time entries have been successfully marked as not processed at %2$s.', sb()->bold('$amount'), '$time'))
            ->none(t('No time entries selected that could be marked as not processed.'));

        $this->setProcessedFlag($redirect, $action->getSelectedValues(), false);

        $redirect->redirect();
    }

    private function setProcessedFlag(UI_DataGrid_RedirectMessage $redirect, array $ids, bool $processed) : void
    {
        $this->screen->startTransaction();

        foreach($ids as $id) {
            $redirect->addAffected($this->getCollection()
                ->getByID((int)$id)
                ->setProcessed($processed)
                ->saveChained()
                ->getLabel()
            );
        }

        $this->screen->endTransaction();
    }

    private function resolveRedirectURL() : AdminURLInterface
    {
        $collection = AppFactory::createTimeTracker();

        if($this->screen instanceof DayListSubmode) {
            return $collection->adminURL()->dayList();
        }

        return $collection->adminURL()->list();
    }

    protected function preRender(): void
    {
    }

    /**
     * @var TimeEntry[]
     */
    private array $summaryEntries = array();

    protected function collectEntry(object $record): UI_DataGrid_Entry
    {
        $timeEntry = ClassHelper::requireObjectInstanceOf(
            TimeEntry::class,
            $record
        );

        if($this->summaryEnabled) {
            $this->summaryEntries[] = $timeEntry;
        }

        $duration = $timeEntry->getDuration();

        $this->totalDuration += $timeEntry->getDuration()->getTotalSeconds();

        $grid = $this->getDataGrid();
        $entry = $grid->createEntry();

        $entry->setColumnValue(self::COL_ID, $timeEntry->getID());
        $entry->setColumnValue(self::COL_DATE, sb()->link(ConvertHelper::date2listLabel($timeEntry->getDate(), false, true), $timeEntry->adminURL()->status()));
        $entry->setColumnValue(self::COL_DURATION, $this->renderDuration($timeEntry, $duration));
        $entry->setColumnValue(self::COL_END_TIME, $entry->renderCheckboxLabel($timeEntry->getEndTime()->toReadable()));
        $entry->setColumnValue(self::COL_START_TIME, $entry->renderCheckboxLabel($timeEntry->getStartTime()->toReadable()));
        $entry->setColumnValue(self::COL_TYPE, $timeEntry->getType()->getLabel());
        $entry->setColumnValue(self::COL_TICKET, $timeEntry->renderTicket());
        $entry->setColumnValue(self::COL_PROCESSED, UI::prettyBool($timeEntry->isProcessed())->makeYesNo());
        $entry->setColumnValue(self::COL_COMMENTS, $timeEntry->renderComments());

        return $entry;
    }

    private function renderDuration(TimeEntry $timeEntry, DurationStringInfo $duration) : string
    {
        $text = sb();
        if($this->isDayMode()) {
            $text->link($duration->getNormalized(), $timeEntry->adminURL()->settings());
        } else {
            $text->add($duration->getNormalized());
        }

        return (string)$text
            ->add('&#160;')
            ->muted(TimeEntry::duration2hoursDec($duration));
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
