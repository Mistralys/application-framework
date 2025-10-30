<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\ListScreen;

use Application\AppFactory;
use Application\TimeTracker\Admin\TimeListBuilder;
use Application\TimeTracker\Admin\TimeUIManager;
use Application\TimeTracker\TimeSpans\SidebarSpans;
use Application\TimeTracker\TimeSpans\TimeSpanRecord;
use Application\TimeTracker\TimeTrackerCollection;
use Application_Admin_Area_Mode_Submode;
use AppUtils\ConvertHelper;
use AppUtils\HTMLTag;
use AppUtils\Microtime;
use DateInterval;
use Throwable;
use UI;
use UI\DataGrid\ListBuilder\ListBuilderScreenInterface;
use UI\DataGrid\ListBuilder\ListBuilderScreenTrait;
use UI\Interfaces\ListBuilderInterface;
use UI_Button;
use UI_Renderable_Interface;

/**
 * @method TimeListBuilder getBuilder()
 */
class BaseDayListScreen extends Application_Admin_Area_Mode_Submode implements ListBuilderScreenInterface
{
    use ListBuilderScreenTrait;

    public const string URL_NAME = 'day';
    public const string LIST_ID = 'time-entries-day';
    public const string REQUEST_VAR_DATE = 'date';

    private Microtime $date;
    private TimeTrackerCollection $timeTracker;
    private Microtime $previousDay;
    private Microtime $nextDay;

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
        return (new TimeListBuilder($this))
            ->enableDayMode($this->date)
            ->enableSummary();
    }

    /**
     * @param TimeListBuilder $builder
     * @return string
     */
    protected function _renderBelowList(ListBuilderInterface $builder): string
    {
        return $builder->renderTicketSummary();
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getSubtitle()
            ->setText(sb()
                ->add(ConvertHelper::date2dayName($this->date))
                ->sf('%02d.', $this->date->getDay())
                ->add(ConvertHelper::month2string($this->date->getMonth()))
                ->add($this->date->getYear())
            )
            ->addContextElement($this->renderDateNavigation());
    }

    private function renderDateNavigation() : UI_Renderable_Interface
    {
        $content =
            $this->ui->createButtonGroup()
            ->addButton(UI::button(UI::icon()->previous().' '.t('Previous day'))
                ->link($this->timeTracker->adminURL()->dayList($this->previousDay))
            )
            ->addButton(
                UI::button(t('Next day').' '.UI::icon()->next())
                    ->link($this->timeTracker->adminURL()->dayList($this->nextDay))
            )
            ->addButton($this->getButtonToday()).
        HTMLTag::create('div')
            ->addClass('input-append')
            ->style('margin-left', '20px')
            ->setContent(
                HTMLTag::create('input')
                    ->attr('type', 'text')
                    ->attr('placeholder', 'yyyy-mm-dd')
                    ->addClass('input-small').
                UI::button('Go')
            );

        return sb()->html($content);
    }

    protected function getButtonToday() : UI_Button
    {
        $btn = UI::button(t('Today'))
            ->link($this->timeTracker->adminURL()->dayList(Microtime::createNow()))
            ->setTooltip(t('Jump to today'));

        if(!$this->date->isToday()) {
            $btn->makePrimary();
        }

        return $btn;
    }

    protected function _handleCustomActions(): void
    {
        TimeUIManager::setLastUsedList(TimeUIManager::LIST_SCREEN_DAY);

        $this->date = TimeUIManager::getLastUsedDate();

        if($this->request->hasParam(self::REQUEST_VAR_DATE)) {
            try {
                $this->date = Microtime::createFromString((string)$this->request->getParam(self::REQUEST_VAR_DATE));
                TimeUIManager::setLastUsedDate($this->date);
            } catch (Throwable $e) {

            }
        }

        $this->timeTracker = AppFactory::createTimeTracker();

        $this->previousDay = clone $this->date;
        $this->previousDay->sub(DateInterval::createFromDateString('1 day'));

        $this->nextDay = clone $this->date;
        $this->nextDay->add(DateInterval::createFromDateString('1 day'));
    }

    protected function _handleSidebarTop(): void
    {
        $this->sidebar->addButton('create', t('Create new entry').'...')
            ->setIcon(UI::icon()->add())
            ->link(AppFactory::createTimeTracker()->adminURL()->create());

        $this->sidebar->addSeparator();

        $this->addSidebarTimeSpans();

        $this->sidebar->addSeparator();
    }

    private function addSidebarTimeSpans() : void
    {
        (new SidebarSpans($this->date, $this->sidebar))->addItems();
    }

    protected function _handleSidebarBottom(): void
    {
    }
}
