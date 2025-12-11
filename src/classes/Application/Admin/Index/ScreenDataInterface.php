<?php

declare(strict_types=1);

namespace Application\Admin\Index;

interface ScreenDataInterface
{
    public const string KEY_SCREEN_NAVIGATION_TITLE = 'navigationTitle';
    public const string KEY_SCREEN_REQUIRED_RIGHT = 'requiredRight';
    public const string KEY_SCREEN_CLASS = 'class';
    public const string KEY_SCREEN_PATH = 'path';
    public const string KEY_SCREEN_ID = 'id';
    public const string KEY_SCREEN_URL_PATH = 'urlPath';
    public const string KEY_SCREEN_TITLE = 'title';
    public const string KEY_SCREEN_FEATURE_RIGHTS = 'featureRights';
    public const string KEY_SCREEN_URL_NAME = 'urlName';
    public const string KEY_ROOT_URL_PATHS = 'urlPaths';
    public const string KEY_ROOT_FLAT = 'flat';
    public const string KEY_ROOT_TREE = 'tree';
    public const string KEY_SCREEN_SUBSCREEN_CLASSES = 'subscreenClasses';
    public const string KEY_SCREEN_SUBSCREENS = 'subscreens';
}
