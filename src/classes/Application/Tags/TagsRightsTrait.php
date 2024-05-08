<?php

declare(strict_types=1);

namespace Application\Tags;

use Application_User_Rights_Group;

trait TagsRightsTrait
{
    public function canViewTags() : bool { return $this->can(TagsRightsInterface::RIGHT_VIEW_TAGS); }
    public function canCreateTags() : bool { return $this->can(TagsRightsInterface::RIGHT_CREATE_TAGS); }
    public function canEditTags() : bool { return $this->can(TagsRightsInterface::RIGHT_EDIT_TAGS); }
    public function canDeleteTags() : bool { return $this->can(TagsRightsInterface::RIGHT_DELETE_TAGS); }

    protected function registerTagRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(self::RIGHT_DELETE_TAGS, t('Delete tags'))
            ->actionDelete()
            ->setDescription(t('Delete tags.'))
            ->grantRight(self::RIGHT_CREATE_TAGS);

        $group->registerRight(self::RIGHT_CREATE_TAGS, t('Create tags'))
            ->actionCreate()
            ->setDescription(t('Create tags.'))
            ->grantRight(self::RIGHT_EDIT_TAGS);

        $group->registerRight(self::RIGHT_EDIT_TAGS, t('Edit tags'))
            ->actionEdit()
            ->setDescription(t('Edit tags.'))
            ->grantRight(self::RIGHT_VIEW_TAGS);

        $group->registerRight(self::RIGHT_VIEW_TAGS, t('View tags'))
            ->actionView()
            ->setDescription(t('View tags.'));
    }
}
