<?php

declare(strict_types=1);

namespace Application\Media\Admin;

use Application\Media\MediaRightsInterface;

class MediaScreenRights
{
    public const string SCREEN_VIEW_TAGS = MediaRightsInterface::RIGHT_EDIT_MEDIA;
    public const string SCREEN_CREATE = MediaRightsInterface::RIGHT_CREATE_MEDIA;
    public const string SCREEN_LIST = MediaRightsInterface::RIGHT_VIEW_MEDIA;
    public const string SCREEN_VIEW = MediaRightsInterface::RIGHT_VIEW_MEDIA;
    public const string SCREEN_VIEW_SETTINGS = MediaRightsInterface::RIGHT_VIEW_MEDIA;
    public const string SCREEN_VIEW_SETTINGS_EDIT = MediaRightsInterface::RIGHT_EDIT_MEDIA;
    public const string SCREEN_VIEW_STATUS = MediaRightsInterface::RIGHT_VIEW_MEDIA;
}
