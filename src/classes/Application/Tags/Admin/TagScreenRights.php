<?php

declare(strict_types=1);

namespace Application\Tags\Admin;

use Application\Tags\TagsRightsInterface;

class TagScreenRights
{
    public const string SCREEN_VIEW_TAG_TREE = TagsRightsInterface::RIGHT_EDIT_TAGS;
    public const string SCREEN_CREATE = TagsRightsInterface::RIGHT_CREATE_TAGS;
    public const string SCREEN_LIST = TagsRightsInterface::RIGHT_VIEW_TAGS;
    public const string SCREEN_VIEW = TagsRightsInterface::RIGHT_VIEW_TAGS;
    public const string SCREEN_VIEW_SETTINGS = TagsRightsInterface::RIGHT_VIEW_TAGS;
    public const string SCREEN_VIEW_SETTINGS_EDIT = TagsRightsInterface::RIGHT_EDIT_TAGS;
}
