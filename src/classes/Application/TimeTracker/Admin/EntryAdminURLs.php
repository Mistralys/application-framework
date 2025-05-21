<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\TimeTracker\Admin\Screens\BaseViewScreen;
use Application\TimeTracker\Admin\Screens\ViewScreen\BaseSettingsScreen;
use Application\Admin\Area\TimeTracker\ViewScreen\BaseStatusScreen;
use Application\TimeTracker\Admin\Screens\BaseTimeTrackerArea;
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
            ->submode(BaseStatusScreen::URL_NAME);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseSettingsScreen::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseTimeTrackerArea::URL_NAME)
            ->mode(BaseViewScreen::URL_NAME)
            ->int(TimeTrackerCollection::REQUEST_PARAM_ENTRY, $this->entry->getID());
    }
}