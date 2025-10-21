<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens;

use Application\AppFactory;
use Application\TimeTracker\Admin\Screens\AutoFillScreen\WorkBlock;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\Types\TimeEntryTypes;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\Microtime;
use DateInterval;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\TimeSettingsManager;
use Application\TimeTracker\TimeTrackerCollection;
use UI;
use UI\CSSClasses;
use UI_Page_Section;
use UI_Themes_Theme_ContentRenderer;

abstract class BaseAutoFillScreen extends Application_Admin_Area_Mode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'auto-fill';
    public const string FORM_NAME = 'auto-fill-times';
    public const int VARIATION_PERCENT_MIN = 20;
    public const int VARIATION_PERCENT_MAX = 100;
    public const string SETTING_START_TIME_MAX = 'start_time_max';
    public const string SETTING_START_TIME_MIN = 'start_time_min';
    public const string SETTING_WORK_HOURS = 'work_hours_per_day';
    public const string SETTING_OVERTIME_BIAS = 'overtime_bias';
    public const string SETTING_MAX_OVERTIME = 'max_overtime';
    public const string SETTING_MAX_UNDERTIME = 'max_undertime';
    public const string SETTING_DATE = 'date';
    public const string SETTING_LUNCH_MIN_START_TIME = 'lunch_start_min';
    public const string SETTING_LUNCH_MAX_START_TIME = 'lunch_start_max';
    public const string SETTING_LUNCH_MAX_DURATION = 'lunch_duration_max';
    public const string SETTING_LUNCH_MIN_DURATION = 'lunch_duration_min';
    public const string REQUEST_PARAM_CREATE_ENTRIES = 'create_entries';
    public const string SETTING_GENERATION_SETTINGS = 'generation_settings';
    public const string KEY_TIME_START = 'time_start';
    public const string KEY_WORK_DURATION_SECONDS = 'work_duration_seconds';
    public const string KEY_WORK_BLOCKS = 'work_blocks';
    private string $createID;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Auto-fill time entries');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createTimeTracker()->adminURL()->list());
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_CREATE_ENTRY;
    }

    public function getNavigationTitle(): string
    {
        return t('Auto-fill');
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        $this->createID = nextJSID();

        $this->createSettingsForm();

        if($this->isFormValid())
        {
            $values = ArrayDataCollection::create($this->getFormValues());

            $this->savePreferences($values);

            if($this->request->getBool(self::REQUEST_PARAM_CREATE_ENTRIES)) {
                $this->handleCreateEntries($values);
            }

            $this->handleCalculate($values);
        }

        return true;
    }

    private function handleCalculate(ArrayDataCollection $values) : void
    {
        $date = $values->requireMicrotime(self::SETTING_DATE);

        $dayStartTime = $this->calculateStartTime($values);

        $dayWorkHours = $this->getBiasedWorkHours(
            baseHours: $values->getFloat(self::SETTING_WORK_HOURS),
            minDelta: -$values->getFloat(self::SETTING_MAX_UNDERTIME),
            maxDelta: $values->getFloat(self::SETTING_MAX_OVERTIME),
            bias: $values->getFloat(self::SETTING_OVERTIME_BIAS)
        );

        $entries = AppFactory::createTimeTracker()
            ->getFilterCriteria()
            ->setFixedDate($date)
            ->getItemsObjects();

        $workBlocks = array();

        // Build a list of occupied intervals (seconds from midnight) and compute already worked seconds
        $occupied = array();
        $existingWorkedSeconds = 0;

        foreach($entries as $entry) {
            $entryStartTime = $entry->getStartTime();
            $entryEndTime = $entry->getEndTime();

            if($entryStartTime->isEmpty() || $entryEndTime->isEmpty()) {
                continue;
            }

            $s = $entryStartTime->getTotalSeconds();
            $e = $entryEndTime->getTotalSeconds();

            // ignore overnight/invalid entries where end is not after start
            if($e <= $s) {
                continue;
            }

            $occupied[] = array($s, $e);
            $existingWorkedSeconds += ($e - $s);
        }

        $occupied[] = $this->calculateLunch($values);

        // sort by start
        usort($occupied, static function($a, $b) : int { return $a[0] <=> $b[0]; });

        // Merge overlapping/adjacent intervals so gaps are computed correctly
        $merged = array();
        foreach($occupied as $intv) {
            [$s, $e] = $intv;
            if(empty($merged)) {
                $merged[] = [$s, $e];
                continue;
            }

            $lastIndex = count($merged) - 1;
            if($s <= $merged[$lastIndex][1]) {
                // overlap or contiguous - extend
                if($e > $merged[$lastIndex][1]) {
                    $merged[$lastIndex][1] = $e;
                }
            } else {
                $merged[] = [$s, $e];
            }
        }

        $occupied = $merged;

        $desiredSeconds = (int)round($dayWorkHours * 3600.0);
        // round to nearest 5 minutes
        $desiredSeconds = (int)(round($desiredSeconds / 300) * 300);

        $remainingSeconds = $desiredSeconds - $existingWorkedSeconds;

        if($remainingSeconds <= 0) {
            // nothing to add
            return;
        }

        // Convert day start Microtime to seconds since midnight
        $dayStartSeconds = ($dayStartTime->getHour24() * 3600) + ($dayStartTime->getMinutes() * 60) + $dayStartTime->getSeconds();

        $pointer = $dayStartSeconds;

        foreach($occupied as $interval)
        {
            [$startSec, $endSec] = $interval;

            $this->addToStack(
                'occupied',
                DaytimeStringInfo::fromSeconds($startSec),
                $endSec - $startSec
            );

            // occupied interval is before pointer
            if($endSec <= $pointer) {
                continue;
            }

            // pointer inside occupied -> move to its end
            if($startSec <= $pointer) {
                $pointer = $endSec;
                continue;
            }

            // gap between pointer and next occupied start
            if($startSec > $pointer) {
                $gap = $startSec - $pointer;
                $take = min($gap, $remainingSeconds);

                $this->addToStack(
                    'generated',
                    DaytimeStringInfo::fromSeconds($pointer),
                    $take
                );

                $remainingSeconds -= $take;
                $pointer += $take;

                if($remainingSeconds <= 0) {
                    break;
                }

                // advance pointer to the occupied start if there's remaining gap left
                if($pointer < $startSec) {
                    $pointer = $startSec;
                }
            }

            // ensure pointer moves past occupied interval
            if($pointer < $endSec) {
                $pointer = $endSec;
            }
        }

        // If still need time, append after the last pointer (until midnight)
        if($remainingSeconds > 0 && $pointer < 24 * 3600) {
            $this->addToStack(
                'generated',
                DaytimeStringInfo::fromSeconds($pointer),
                $remainingSeconds
            );

            $remainingSeconds = 0;
        }

        $this->workBlocks = $workBlocks;
        $this->startTime = $dayStartTime;
        $this->workDuration = $desiredSeconds;

        usort($this->stack, static function(WorkBlock $a, WorkBlock $b) : int {
            return $a->getStartTime()->getTotalSeconds() <=> $b->getStartTime()->getTotalSeconds();
        });

        $this->renderer->appendContent($this->renderCalculation());

        $this->addHiddenVar(self::REQUEST_PARAM_CREATE_ENTRIES, 'no', $this->createID);

        $this->addHiddenVar(
            self::SETTING_GENERATION_SETTINGS,
            JSONConverter::var2json($this->serializeWorkBlocks())
        );
    }

    private function serializeWorkBlocks() : array
    {
        $result = array();

        foreach($this->stack as $block) {
            $result[] = $block->serialize();
        }

        return $result;
    }

    private function addToStack(string $type, DaytimeStringInfo $startTime, int $duration) : void
    {
        $this->stack[] = new WorkBlock($type, $startTime, $duration);
    }

    private array $workBlocks = array();
    private Microtime $startTime;
    private float $workDuration;

    /**
     * @var WorkBlock[]
     */
    private array $stack = array();

    private function calculateLunch(ArrayDataCollection $values) : array
    {
        $lunchMin = DaytimeStringInfo::fromString($values->getString(self::SETTING_LUNCH_MIN_START_TIME));
        $lunchMax = DaytimeStringInfo::fromString($values->getString(self::SETTING_LUNCH_MAX_START_TIME));
        $minDuration = $values->getInt(self::SETTING_LUNCH_MIN_DURATION);
        $maxDuration = $values->getInt(self::SETTING_LUNCH_MAX_DURATION);

        $lunchStart = random_int($lunchMin->getTotalSeconds(), $lunchMax->getTotalSeconds());
        $lunchStart = (int)(round($lunchStart / 300) * 300);

        $lunchDuration = random_int($minDuration * 60, $maxDuration * 60);
        $lunchDuration = (int)(round($lunchDuration / 300) * 300);

        $lunchEnd = min($lunchStart + $lunchDuration, 24 * 3600);
        return array($lunchStart, $lunchEnd);
    }

    private function getBiasedWorkHours(float $baseHours = 8.0, float $minDelta = -2.0, float $maxDelta = 4.0, float $bias = 2.0): float
    {
        // $bias > 1: more likely to get higher hours; $bias < 1: more likely to get lower hours
        $rand = mt_rand() / mt_getrandmax();
        $biased = $rand ** (1 / $bias); // Skew towards higher values if bias > 1
        $delta = $minDelta + ($maxDelta - $minDelta) * $biased;

        // round to nearest 0.05 hours
        $delta = round($delta * 20) / 20.0;

        return round($baseHours + $delta, 2);
    }

    private function calculateStartTime(ArrayDataCollection $values) : Microtime
    {
        $earliestTime = DaytimeStringInfo::fromString($values->getString(self::SETTING_START_TIME_MIN));
        $latestTime = DaytimeStringInfo::fromString($values->getString(self::SETTING_START_TIME_MAX));

        $span = $latestTime->getTotalSeconds() - $earliestTime->getTotalSeconds();

        $randOffset = random_int(0, $span);

        // round the offset to the nearest 5 minutes
        $randOffset = (int)(round($randOffset / 300) * 300);

        $start = sprintf(
            '%s %s:00',
            $values->requireMicrotime(self::SETTING_DATE)->format('Y-m-d'),
            DaytimeStringInfo::fromSeconds($earliestTime->getTotalSeconds() + $randOffset)->getNormalized()
        );

        return Microtime::createFromString($start);
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->wizard());
    }

    protected function _handleSidebar(): void
    {
        if(!$this->isFormSubmitted()) {
            $this->sidebar->addButton('roll', t('Calculate'))
                ->setIcon(UI::icon()->calculate())
                ->makePrimary()
                ->makeClickableSubmit($this);
            return;
        }

        $this->sidebar->addButton('create_entries', t('Create entries'))
            ->makePrimary()
            ->setIcon(UI::icon()->add())
            ->setTooltip(t('Creates the time entries as shown in the calculation.'))
            ->click(sprintf(
                "document.getElementById('%s').value='yes'; %s",
                $this->createID,
                $this->formableForm->getJSSubmitHandler()
            ));

        $this->sidebar->addButton('re_roll', t('Re-roll calculations'))
            ->setIcon(UI::icon()->calculate())
            ->setTooltip(t('Generate a new set of time entries.'))
            ->makeClickableSubmit($this);
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function renderCalculation() : UI_Page_Section
    {
        $sel = $this->ui->createBigSelection();

        foreach($this->stack as $item)
        {
            $entry = $sel->addItem(sb()
                ->add($item->getStartTime()->getNormalized())
                ->add('-')
                ->add($item->getEndTime()->getNormalized())
                ->add($item->getBadge())
            );

            $entry->setDescription(sb()
                ->t('Duration:')
                ->add($item->getDurationPretty())
            );
        }

        $props = $this->ui->createPropertiesGrid();
        $props->setLabelWidth(15);
        $props->add(t('Date'), sb()->link($this->startTime->format('Y-m-d'), AppFactory::createTimeTracker()->adminURL()->dayList($this->startTime), true));
        $props->add(t('Start time'), $this->startTime->format('H:i:s'));
        $props->add(t('Total hours'), ConvertHelper::interval2string(DateInterval::createFromDateString($this->workDuration.' seconds')));

        return $this->ui->createSection()
            ->setTitle(t('Calculated time entries'))
            ->setIcon(UI::icon()->wizard())
            ->setContent(sb()
                ->add($props)
                ->add($sel)
            );
    }

    private function getDefaultFormValues() : array
    {
        $data = TimeUIManager::getAutoFillPreferences()->getData();
        $data[self::SETTING_DATE] = TimeUIManager::getLastUsedDate()->format('Y-m-d');
        return $data;
    }

    public static function getDefaultPreferences() : array
    {
        return array(
            self::SETTING_START_TIME_MIN => '07:00',
            self::SETTING_START_TIME_MAX => '08:00',
            self::SETTING_WORK_HOURS => '8.0',
            self::SETTING_MAX_OVERTIME => '3.0',
            self::SETTING_MAX_UNDERTIME => '2.0',
            self::SETTING_OVERTIME_BIAS => '1.2',
            self::SETTING_LUNCH_MIN_START_TIME => '12:00',
            self::SETTING_LUNCH_MAX_START_TIME => '12:45',
            self::SETTING_LUNCH_MIN_DURATION => 15,
            self::SETTING_LUNCH_MAX_DURATION => 40,
        );
    }

    private function savePreferences(ArrayDataCollection $values) : void
    {
        $prefs = ArrayDataCollection::create(array(
            self::SETTING_START_TIME_MIN => $values->getString(self::SETTING_START_TIME_MIN),
            self::SETTING_START_TIME_MAX => $values->getString(self::SETTING_START_TIME_MAX),
            self::SETTING_WORK_HOURS => $values->getFloat(self::SETTING_WORK_HOURS),
            self::SETTING_MAX_OVERTIME => $values->getFloat(self::SETTING_MAX_OVERTIME),
            self::SETTING_MAX_UNDERTIME => $values->getFloat(self::SETTING_MAX_UNDERTIME),
            self::SETTING_OVERTIME_BIAS => $values->getFloat(self::SETTING_OVERTIME_BIAS),
            self::SETTING_LUNCH_MIN_START_TIME => $values->getString(self::SETTING_LUNCH_MIN_START_TIME),
            self::SETTING_LUNCH_MAX_START_TIME => $values->getString(self::SETTING_LUNCH_MAX_START_TIME),
            self::SETTING_LUNCH_MIN_DURATION => $values->getInt(self::SETTING_LUNCH_MIN_DURATION),
            self::SETTING_LUNCH_MAX_DURATION => $values->getInt(self::SETTING_LUNCH_MAX_DURATION)
        ));

        TimeUIManager::setAutoFillPreferences($prefs);
    }

    private function createSettingsForm() : void
    {
        $this->createFormableForm(self::FORM_NAME, $this->getDefaultFormValues());

        $this->addSection(t('Generation details'))
            ->expand()
            ->setIcon(UI::icon()->information());

        $this->injectDate();

        $this->addSection(t('General Settings'))
            ->setIcon(UI::icon()->settings())
            ->collapse();

        $this->injectWorkHours();

        $this->addSection(t('Starting time Settings'))
            ->setAbstract(t('The starting time will be randomized between the minimum and maximum values specified here.'))
            ->setIcon(UI::icon()->settings())
            ->collapse();

        $this->injectEarliestTime();
        $this->injectLatestStartTime();

        $this->addSection(t('Overtime Settings'))
            ->setIcon(UI::icon()->settings())
            ->collapse();

        $this->injectMaxOvertime();
        $this->injectMaxUndertime();
        $this->injectOvertimeBias();

        $this->addSection(t('Lunch Break Settings'))
            ->setIcon(UI::icon()->settings())
            ->collapse();

        $this->injectLunchStart();
        $this->injectLunchEnd();
        $this->injectLunchMinDuration();
        $this->injectLunchMaxDuration();
    }

    private function injectLunchStart() : void
    {
        $el = $this->addElementText(self::SETTING_LUNCH_MIN_START_TIME, t('Minimum start time'))
            ->addClass(CSSClasses::INPUT_XSMALL)
            ->setComment(t('Preferred start time for lunch break.'));

        $this->setElementAppend($el, t('HH:MM'));
    }

    private function injectLunchEnd() : void
    {
        $el = $this->addElementText(self::SETTING_LUNCH_MAX_START_TIME, t('Maximum start time'))
            ->addClass(CSSClasses::INPUT_XSMALL)
            ->setComment(t('Latest start time for lunch break.'));

        $this->setElementAppend($el, t('HH:MM'));
    }

    private function injectLunchMinDuration() : void
    {
        $el = $this->addElementInteger(self::SETTING_LUNCH_MIN_DURATION, t('Min duration'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('Minutes'));
    }

    private function injectLunchMaxDuration() : void
    {
        $el = $this->addElementInteger(self::SETTING_LUNCH_MAX_DURATION, t('Max duration'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('Minutes'));
    }

    private function injectDate() : void
    {
        $el = $this->addElementISODate(self::SETTING_DATE, t('Date'));

        $this->makeRequired($el);
    }

    private function injectEarliestTime() : void
    {
        $el = $this->addElementText(self::SETTING_START_TIME_MIN, t('Earliest start time'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('HH:MM'));
    }

    private function injectLatestStartTime() : void
    {
        $el = $this->addElementText(self::SETTING_START_TIME_MAX, t('Latest start time'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('HH:MM'));
    }

    private function injectWorkHours() : void
    {
        $el = $this->addElementText(self::SETTING_WORK_HOURS,t('Work per day'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('Hours'));
    }

    private function injectMaxOvertime() : void
    {
        $el = $this->addElementText(self::SETTING_MAX_OVERTIME, t('Maximum overtime'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('Hours'));
    }

    private function injectMaxUndertime() : void
    {
        $el = $this->addElementText(self::SETTING_MAX_UNDERTIME, t('Maximum undertime'))
            ->addClass(CSSClasses::INPUT_XSMALL);

        $this->setElementAppend($el, t('Hours'));
    }

    private function injectOvertimeBias() : void
    {
        $el = $this->addElementText(self::SETTING_OVERTIME_BIAS, t('Overtime bias'));
        $el->addClass(CSSClasses::INPUT_XSMALL);
        $el->setComment(sb()
            ->t('How biased the calculation is towards generating overtime.')
            ->t('Higher than 1: More likely to get higher hours; Lesser than 1: More likely to get lower hours.')
        );
    }

    private function handleCreateEntries(ArrayDataCollection $values) : never
    {
        $data = JSONConverter::json2array($this->request->registerParam(self::SETTING_GENERATION_SETTINGS)->setJSON()->getString());

        $date = $values->requireMicrotime(self::SETTING_DATE);
        $workBlocks = array();

        foreach($data as $def) {
            $workBlocks[] = WorkBlock::fromSerialized($def);
        }

        $tracker = AppFactory::createTimeTracker();
        $count = 0;

        $this->startTransaction();

        foreach($workBlocks as $block)
        {
            if($block->isOccupied()) {
                continue;
            }

            $count++;

            $tracker->createNewEntryByTime(
                $date,
                $block->getStartTime(),
                $block->getEndTime(),
                TimeEntryTypes::getInstance()->getDefault()
            );
        }

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t(
                '%1$s time entries have been created successfully at %1$s.',
                $count,
                sb()->time()
            ),
            AppFactory::createTimeTracker()->adminURL()->dayList()
        );
    }


}
