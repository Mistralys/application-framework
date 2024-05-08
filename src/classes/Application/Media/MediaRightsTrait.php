<?php

declare(strict_types=1);

namespace Application\Media;

use Application_User_Rights_Group;

trait MediaRightsTrait
{
    public function canViewMedia() : bool { return $this->can(MediaRightsInterface::RIGHT_VIEW_MEDIA); }
    public function canCreateMedia() : bool { return $this->can(MediaRightsInterface::RIGHT_CREATE_MEDIA); }
    public function canEditMedia() : bool { return $this->can(MediaRightsInterface::RIGHT_EDIT_MEDIA); }
    public function canDeleteMedia() : bool { return $this->can(MediaRightsInterface::RIGHT_DELETE_MEDIA); }
    public function canAdministrateMedia() : bool { return $this->can(MediaRightsInterface::RIGHT_ADMIN_MEDIA); }

    protected function registerMediaRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(self::RIGHT_ADMIN_MEDIA, t('Administrate media'))
            ->actionAdministrate()
            ->setDescription(t('Administrate global media settings.'))
            ->grantRight(self::RIGHT_DELETE_MEDIA);

        $group->registerRight(self::RIGHT_DELETE_MEDIA, t('Delete media'))
            ->actionDelete()
            ->setDescription(t('Delete media files.'))
            ->grantRight(self::RIGHT_CREATE_MEDIA);

        $group->registerRight(self::RIGHT_CREATE_MEDIA, t('Add media'))
            ->actionCreate()
            ->setDescription(t('Add media files.'))
            ->grantRight(self::RIGHT_EDIT_MEDIA);

        $group->registerRight(self::RIGHT_EDIT_MEDIA, t('Edit media'))
            ->actionEdit()
            ->setDescription(t('Modify media files.'))
            ->grantRight(self::RIGHT_VIEW_MEDIA);

        $group->registerRight(self::RIGHT_VIEW_MEDIA, t('View media'))
            ->actionView()
            ->setDescription(t('View media files.'));
    }
}
