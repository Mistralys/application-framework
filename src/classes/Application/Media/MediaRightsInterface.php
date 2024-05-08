<?php

declare(strict_types=1);

namespace Application\Media;

use Application_User_Interface;

interface MediaRightsInterface extends Application_User_Interface
{
    public const RIGHT_CREATE_MEDIA = 'CreateMedia';
    public const RIGHT_EDIT_MEDIA = 'EditMedia';
    public const RIGHT_DELETE_MEDIA = 'DeleteMedia';
    public const RIGHT_VIEW_MEDIA = 'ViewMedia';
    public const RIGHT_ADMIN_MEDIA = 'AdminMedia';
}
