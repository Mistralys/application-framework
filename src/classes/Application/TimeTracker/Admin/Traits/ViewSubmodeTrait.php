<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Traits;

use Application\AppFactory;
use Application\TimeTracker\Admin\Screens\Mode\ViewMode;
use Application\TimeTracker\Admin\Screens\TimeTrackerArea;
use Application\TimeTracker\TimeTrackerCollection;
use UI\AdminURLs\AdminURLInterface;

trait ViewSubmodeTrait
{
    public function createCollection() : TimeTrackerCollection
    {
        return AppFactory::createTimeTracker();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return AppFactory::createTimeTracker()->adminURL()->list();
    }

    public function getParentScreenClass() : string
    {
        return ViewMode::class;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass() : ?string
    {
        return null;
    }
}
