<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Traits;

use Application\Admin\ClassLoaderScreenInterface;
use Application\AppFactory;
use Application\Interfaces\Admin\AdminModeInterface;
use Application\TimeTracker\Admin\Screens\TimeTrackerArea;
use Application\TimeTracker\TimeTrackerCollection;
use UI\AdminURLs\AdminURLInterface;

trait ModeTrait
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
        return TimeTrackerArea::class;
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
