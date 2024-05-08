<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application_User_Interface;

interface NewsRightsInterface extends Application_User_Interface
{
    public const RIGHT_CREATE_NEWS = 'CreateNews';
    public const RIGHT_CREATE_NEWS_ALERTS = 'CreateAlerts';
    public const RIGHT_EDIT_NEWS = 'EditNews';
    public const RIGHT_DELETE_NEWS = 'DeleteNews';
    public const RIGHT_VIEW_NEWS = 'ViewNews';
}
