<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\BaseAutoFillScreen;
use Application\TimeTracker\Admin\Screens\BaseCreateScreen;
use Application\TimeTracker\Admin\Screens\BaseExportScreen;
use Application\TimeTracker\Admin\Screens\BaseImportScreen;
use Application\TimeTracker\Admin\Screens\BaseListScreen;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseDayListScreen;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseGlobalListScreen;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseGlobalSettingsScreen;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseTimeSpansListScreen;
use AppUtils\Microtime;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class TrackerAdminURLs
{
    public function create() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseCreateScreen::URL_NAME);
    }

    public function autoFill() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseAutoFillScreen::URL_NAME);
    }

    public function list() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseListScreen::URL_NAME);
    }

    public function export() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseExportScreen::URL_NAME);
    }

    public function exportConfirm() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseExportScreen::URL_NAME)
            ->bool(BaseExportScreen::REQUEST_PARAM_CONFIRM, true);
    }

    public function import() : AdminURLInterface
    {
        return $this->base()
            ->mode(BaseImportScreen::URL_NAME);
    }

    public function globalList() : AdminURLInterface
    {
        return $this->list()
            ->submode(BaseGlobalListScreen::URL_NAME);
    }
    public function dayList(?Microtime $targetDate=null) : AdminURLInterface
    {
        $url = $this->list()
            ->submode(BaseDayListScreen::URL_NAME);

        if($targetDate !== null) {
            $url->string(BaseDayListScreen::REQUEST_VAR_DATE, $targetDate->format('Y-m-d'));
        }

        return $url;
    }

    public function timeSpans() : AdminURLInterface
    {
        return $this->list()
            ->submode(BaseTimeSpansListScreen::URL_NAME);
    }

    public function globalSettings() : AdminURLInterface
    {
        return $this->list()
            ->submode(BaseGlobalSettingsScreen::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseTimeTrackerArea::URL_NAME);
    }
}