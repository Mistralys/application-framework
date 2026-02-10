<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Admin;

use Application\Admin\ScreenRightsContainerInterface;
use Application\Admin\ScreenRightsContainerTrait;
use Application\Admin\ScreenRightsInterface;
use Application\NewsCentral\Admin\NewsScreens;

class ScreenRightsContainerStub implements ScreenRightsContainerInterface
{
    use ScreenRightsContainerTrait;

    protected function createAdminScreens(): ScreenRightsInterface
    {
        return new NewsScreens();
    }
}
