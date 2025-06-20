<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\BaseCreateTimeSpanScreen;
use Application\TimeTracker\Admin\Screens\BaseListScreen;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
use Application\TimeTracker\Admin\Screens\ListScreen\BaseTimeSpansListScreen;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class TimeSpansAdminURLs
{
    public function create(): AdminURLInterface
    {
        return $this->base()
            ->mode(BaseCreateTimeSpanScreen::URL_NAME);
    }

    public function list(): AdminURLInterface
    {
        return $this->base()
            ->mode(BaseListScreen::URL_NAME)
            ->submode(BaseTimeSpansListScreen::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseTimeTrackerArea::URL_NAME);
    }
}
