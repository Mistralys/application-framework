<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer;

use Application;
use Application\AppFactory\ClassCacheHandler;
use Application\Bootstrap\ComposerScriptBootstrap;
use Application\CacheControl\CacheManager;
use Application_Bootstrap;
use Application_Exception;
use AppUtils\FileHelper\FileInfo;

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
    public const int ERROR_BOOTSTRAP_NOT_FOUND = 169801;

    /**
     * Clears the PHP class cache that is used by the {@see CacheManager}.
     * @return void
     */
    public static function clearClassCache() : void
    {
        self::init();

        echo 'Clearing class cache...';
        ClassCacheHandler::clearClassCache();
        echo 'DONE.'.PHP_EOL;
    }

    public static function clearCaches() : void
    {
        self::init();

        echo 'Clearing all caches...';
        CacheManager::getInstance()->clearAll();
        echo 'DONE.'.PHP_EOL;
    }

    private static bool $initialized = false;

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
        if(self::$initialized) {
            return;
        }

        self::$initialized = true;

        if(Application::isInstalledAsDependency()) {
            $bootstrap = Application::detectRootFolder().'/bootstrap.php';
        } else {
            $bootstrap = Application::detectRootFolder().'/tests/bootstrap.php';
        }

        $file = FileInfo::factory($bootstrap);
        if(!$file->exists()) {
            throw new Application_Exception(
                'The bootstrap file could not be found at: '.$bootstrap,
                '',
                self::ERROR_BOOTSTRAP_NOT_FOUND
            );
        }

        require_once (string)$file;

        // When not installed as a dependency, the bootstrapper automatically
        // loads the unit test bootstrapper. In an application, this is not
        // the case so we load the composer bootstrapper to ensure that everything
        // is available, like the database.
        if(!Application_Bootstrap::isBooted()) {
            Application_Bootstrap::bootClass(ComposerScriptBootstrap::class);
        }
    }
}
