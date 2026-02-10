<?php

declare(strict_types=1);

namespace Application\AI;

use Application\Application;
use Application\Bootstrap\Screen\AIToolsBootstrap;
use Application_Bootstrap;

class EnvironmentRunner
{
    public static function run() : void
    {
        if(Application::isActive()) {
            return;
        }

        require_once __DIR__.'/../../../../tests/application/bootstrap.php';

        Application_Bootstrap::bootClass(AIToolsBootstrap::class);
    }
}
