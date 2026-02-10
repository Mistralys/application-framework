<?php

declare(strict_types=1);

namespace Application\Admin\Index\API;

use Application\Admin\Index\ScreenDataInterface;
use Application\API\APIMethodInterface;

interface DescribeAdminAreasAPIInterface extends APIMethodInterface, ScreenDataInterface
{
    public const string KEY_ROOT_SCREENS = 'screens';

}
