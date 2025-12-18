<?php

declare(strict_types=1);

namespace Application\NewsCentral\User;

use Application_User_Rights_Group;

trait NewsRightsTrait
{
    public function canViewNews() : bool { return $this->can(NewsRightsInterface::RIGHT_VIEW_NEWS); }
    public function canCreateNews() : bool { return $this->can(NewsRightsInterface::RIGHT_CREATE_NEWS); }
    public function canCreateNewsAlerts() : bool { return $this->can(NewsRightsInterface::RIGHT_CREATE_NEWS_ALERTS); }
    public function canEditNews() : bool { return $this->can(NewsRightsInterface::RIGHT_EDIT_NEWS); }
    public function canDeleteNews() : bool { return $this->can(NewsRightsInterface::RIGHT_DELETE_NEWS); }

    protected function registerNewsRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(self::RIGHT_DELETE_NEWS, t('Delete news'))
            ->actionDelete()
            ->setDescription(t('Delete news entries.'))
            ->grantRight(self::RIGHT_CREATE_NEWS)
            ->grantRight(self::RIGHT_CREATE_NEWS_ALERTS);

        $group->registerRight(self::RIGHT_CREATE_NEWS_ALERTS, t('Create alerts'))
            ->actionCreate()
            ->setDescription(t('Create and modify news alerts.'))
            ->grantRight(self::RIGHT_CREATE_NEWS);

        $group->registerRight(self::RIGHT_CREATE_NEWS, t('Create news'))
            ->actionCreate()
            ->setDescription(t('Create news entries.'))
            ->grantRight(self::RIGHT_EDIT_NEWS);

        $group->registerRight(self::RIGHT_EDIT_NEWS, t('Edit news'))
            ->actionEdit()
            ->setDescription(t('Edit news entries.'))
            ->grantRight(self::RIGHT_VIEW_NEWS);

        $group->registerRight(self::RIGHT_VIEW_NEWS, t('View news'))
            ->actionView()
            ->setDescription(t('View news entries.'));
    }
}
