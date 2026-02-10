<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\Mode\ViewMode;
use Application\TimeTracker\Admin\Screens\Mode\ViewScreen\SettingsSubmode;
use Application\TimeTracker\Admin\Screens\Mode\ViewScreen\StatusSubmode;
use Application\TimeTracker\Admin\Screens\TimeTrackerArea;
use Application\TimeTracker\TimeEntry;
use Application\TimeTracker\TimeTrackerCollection;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class EntryAdminURLs
{
    private TimeEntry $entry;

    public function __construct(TimeEntry $entry)
    {
        $this->entry = $entry;
    }

    public function status() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(StatusSubmode::URL_NAME);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(SettingsSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(TimeTrackerArea::URL_NAME)
            ->mode(ViewMode::URL_NAME)
            ->int(TimeTrackerCollection::REQUEST_PARAM_ENTRY, $this->entry->getID());
    }
}