<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer;

use Application\AppFactory\ClassCacheHandler;
use Application\CacheControl\CacheManager;

/**
 * Class with static methods that are used as Composer scripts.
 *
 * See the {@see /composer.json} file for the scripts that are defined.
 *
 * @package Application
 * @subpackage Composer
 */
class ComposerScripts
{
    /**
     * Clears the PHP class cache that is used by the {@see CacheManager}.
     * @return void
     */
    public static function clearClassCache() : void
    {
        echo 'Clearing class cache...';
        ClassCacheHandler::clearClassCache();
        echo 'DONE.'.PHP_EOL;
    }

    public static function clearCaches() : void
    {
        echo 'Clearing all caches...';
        CacheManager::getInstance()->clearAll();
        echo 'DONE.'.PHP_EOL;
    }

    /**
     * Loads the bootstrap file for the application.
     *
     * When running within the framework GIT package,
     * the test application's bootstrap file is loaded.
     * Otherwise, the application's bootstrap file is
     * used.
     *
     * This way, the scripts can be used interchangeably
     * when developing the framework and the application.
     *
     * @return void
     */
    public static function init() : void
    {
        $appBoostrap = __DIR__.'/../../../../bootstrap.php';
        $frameworkBootstrap = __DIR__.'/../../../../tests/bootstrap.php';

        if(file_exists($appBoostrap)) {
            require_once $appBoostrap;
            return;
        }

        require_once $frameworkBootstrap;
    }
}

ComposerScripts::init();
