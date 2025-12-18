<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin;

use Application\NewsCentral\User\NewsRightsInterface;
use Application_User;

class NewsScreenRights
{
    /**
     * Must be the same as {@see self::SCREEN_READ_NEWS} to allow access to the read news area.
     */
    public const string SCREEN_NEWS = Application_User::RIGHT_LOGIN;
    public const string SCREEN_NEWS_LIST = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_READ_ARTICLES = Application_User::RIGHT_LOGIN;
    public const string SCREEN_READ_ARTICLE = Application_User::RIGHT_LOGIN;
    public const string SCREEN_READ_NEWS = Application_User::RIGHT_LOGIN;
    public const string SCREEN_VIEW_ARTICLE = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_VIEW_CATEGORIES = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_CATEGORY_SETTINGS = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_CATEGORY_SETTINGS_EDIT = NewsRightsInterface::RIGHT_EDIT_NEWS;
    public const string SCREEN_CATEGORIES_LIST = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_CREATE_CATEGORY = NewsRightsInterface::RIGHT_EDIT_NEWS;
    public const string SCREEN_CREATE_ARTICLE = NewsRightsInterface::RIGHT_CREATE_NEWS;
    public const string SCREEN_CREATE_ALERT = NewsRightsInterface::RIGHT_CREATE_NEWS_ALERTS;
    public const string SCREEN_ARTICLE_STATUS = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_ARTICLE_SETTINGS = NewsRightsInterface::RIGHT_VIEW_NEWS;
    public const string SCREEN_ARTICLE_SETTINGS_EDIT = NewsRightsInterface::RIGHT_EDIT_NEWS;
}
