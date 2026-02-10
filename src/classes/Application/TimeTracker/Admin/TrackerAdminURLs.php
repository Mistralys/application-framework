<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\Mode\AutoFillMode;
use Application\TimeTracker\Admin\Screens\Mode\CreateEntryMode;
use Application\TimeTracker\Admin\Screens\Mode\ExportMode;
use Application\TimeTracker\Admin\Screens\Mode\ImportMode;
use Application\TimeTracker\Admin\Screens\Mode\ListMode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\DayListSubmode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\GlobalListSubmode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\GlobalSettingsSubmode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\TimeSpanListSubmode;
use Application\TimeTracker\Admin\Screens\TimeTrackerArea;
use AppUtils\Microtime;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class TrackerAdminURLs
{
    public function create() : AdminURLInterface
    {
        return $this->base()
            ->mode(CreateEntryMode::URL_NAME);
    }

    public function autoFill() : AdminURLInterface
    {
        return $this->base()
            ->mode(AutoFillMode::URL_NAME);
    }

    public function list() : AdminURLInterface
    {
        return $this->base()
            ->mode(ListMode::URL_NAME);
    }

    public function export() : AdminURLInterface
    {
        return $this->base()
            ->mode(ExportMode::URL_NAME);
    }

    public function exportConfirm() : AdminURLInterface
    {
        return $this->base()
            ->mode(ExportMode::URL_NAME)
            ->bool(ExportMode::REQUEST_PARAM_CONFIRM, true);
    }

    public function import() : AdminURLInterface
    {
        return $this->base()
            ->mode(ImportMode::URL_NAME);
    }

    public function globalList() : AdminURLInterface
    {
        return $this->list()
            ->submode(GlobalListSubmode::URL_NAME);
    }
    public function dayList(?Microtime $targetDate=null) : AdminURLInterface
    {
        $url = $this->list()
            ->submode(DayListSubmode::URL_NAME);

        if($targetDate !== null) {
            $url->string(DayListSubmode::REQUEST_VAR_DATE, $targetDate->format('Y-m-d'));
        }

        return $url;
    }

    public function timeSpans() : AdminURLInterface
    {
        return $this->list()
            ->submode(TimeSpanListSubmode::URL_NAME);
    }

    public function globalSettings() : AdminURLInterface
    {
        return $this->list()
            ->submode(GlobalSettingsSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(TimeTrackerArea::URL_NAME);
    }
}