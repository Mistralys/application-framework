<?php

declare(strict_types=1);

namespace Application\NewsCentral\User;

use Application_User_Interface;

interface NewsRightsInterface extends Application_User_Interface
{
    public const string RIGHT_CREATE_NEWS = 'CreateNews';
    public const string RIGHT_CREATE_NEWS_ALERTS = 'CreateAlerts';
    public const string RIGHT_EDIT_NEWS = 'EditNews';
    public const string RIGHT_DELETE_NEWS = 'DeleteNews';
    public const string RIGHT_VIEW_NEWS = 'ViewNews';
}
