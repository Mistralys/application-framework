<?php

declare(strict_types=1);

namespace Application\Tags;

use Application_User_Interface;

interface TagsRightsInterface extends Application_User_Interface
{
    public const RIGHT_CREATE_TAGS = 'CreateTags';
    public const RIGHT_EDIT_TAGS = 'EditTags';
    public const RIGHT_DELETE_TAGS = 'DeleteTags';
    public const RIGHT_VIEW_TAGS = 'ViewTags';
}
