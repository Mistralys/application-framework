<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\BaseCreateScreen;
use Application\TimeTracker\Admin\Screens\BaseListScreen;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseDayListScreen;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseGlobalListScreen;
use TestDriver\Area\TimeTrackerScreen\ListScreen\DayListScreen;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class TrackerAdminURLs
{
    public function create() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseCreateScreen::URL_NAME);
    }

    public function list() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseListScreen::URL_NAME);
    }

    public function globalList() : AdminURLInterface
    {
        return $this->list()
            ->submode(BaseGlobalListScreen::URL_NAME);
    }
    public function dayList() : AdminURLInterface
    {
        return $this->list()
            ->submode(BaseDayListScreen::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseTimeTrackerArea::URL_NAME);
    }
}