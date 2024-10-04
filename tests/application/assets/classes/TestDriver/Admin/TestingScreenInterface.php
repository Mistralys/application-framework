<?php

declare(strict_types=1);

namespace TestDriver\Admin;

use Application_Admin_ScreenInterface;

interface TestingScreenInterface extends Application_Admin_ScreenInterface
{
    public static function getTestLabel() : string;
}
