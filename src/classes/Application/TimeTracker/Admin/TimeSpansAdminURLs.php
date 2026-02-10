<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\Mode\CreateTimeSpanMode;
use Application\TimeTracker\Admin\Screens\Mode\ListMode;
use Application\TimeTracker\Admin\Screens\Mode\ListScreen\TimeSpanListSubmode;
use Application\TimeTracker\Admin\Screens\TimeTrackerArea;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class TimeSpansAdminURLs
{
    public function create(): AdminURLInterface
    {
        return $this->base()
            ->mode(CreateTimeSpanMode::URL_NAME);
    }

    public function list(): AdminURLInterface
    {
        return $this->base()
            ->mode(ListMode::URL_NAME)
            ->submode(TimeSpanListSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(TimeTrackerArea::URL_NAME);
    }
}
