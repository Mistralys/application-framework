<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer;

use Application;
use Application\AppFactory\ClassCacheHandler;
use Application\CacheControl\CacheManager;
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
    public const ERROR_BOOTSTRAP_NOT_FOUND = 169801;

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
    }
}

ComposerScripts::init();
