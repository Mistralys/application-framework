<?php

declare(strict_types=1);

namespace Application\TimeTracker\User;

use Application_User_Rights;
use Application_User_Rights_Group;
use Closure;

trait TimeTrackerRightsTrait
{
    public function canEditTimeEntries() : bool
    {
        return $this->can(TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES);
    }

    protected function registerTimeTrackingRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES, t('View time entries'))
            ->actionView()
            ->setDescription(t('Allows viewing available time entries.'));

        $group->registerRight(TimeTrackerRightsInterface::RIGHT_CREATE_TIME_ENTRIES, t('Create short messages'))
            ->actionCreate()
            ->setDescription(t('Allows creating time entries.'))
            ->grantRight(TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES)
            ->grantRight(TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES);

        $group->registerRight(TimeTrackerRightsInterface::RIGHT_EDIT_TIME_ENTRIES, t('Edit time entries'))
            ->actionEdit()
            ->setDescription(t('Allows editing time entries.'))
            ->grantRight(TimeTrackerRightsInterface::RIGHT_VIEW_TIME_ENTRIES);

        $group->registerRight(TimeTrackerRightsInterface::RIGHT_DELETE_TIME_ENTRIES, t('Delete time entries'))
            ->actionDelete()
            ->setDescription(t('Allows deleting time entries.'))
            ->grantRight(TimeTrackerRightsInterface::RIGHT_CREATE_TIME_ENTRIES);
    }
}
