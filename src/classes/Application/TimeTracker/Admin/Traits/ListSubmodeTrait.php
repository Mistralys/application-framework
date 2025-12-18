<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Traits;

use Application\AppFactory;
use Application\TimeTracker\Admin\Screens\Mode\ListMode;
use Application\TimeTracker\TimeTrackerCollection;
use UI\AdminURLs\AdminURLInterface;

trait ListSubmodeTrait
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
        return ListMode::class;
    }

    public function getDefaultSubscreenClass() : ?string
    {
        return null;
    }

    public function getDefaultAction(): string
    {
        return '';
    }
}
