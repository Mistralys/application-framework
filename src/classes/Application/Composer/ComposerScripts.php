<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer;

use Application\Admin\Index\AdminScreenIndexer;
use Application\API\APIManager;
use Application\AppFactory;
use Application\AppFactory\ClassCacheHandler;
use Application\ApplicationException;
use Application\CacheControl\CacheManager;
use Application\EventHandler\OfflineEvents\Index\EventClassFinder;
use Application\EventHandler\OfflineEvents\Index\EventIndexer;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
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

    public static function build() : void
    {
        self::init();

        self::clearCaches();
        self::indexOfflineEvents();
        self::indexAdminScreens();
        self::apiMethodIndex();
    }

    public static function clearCaches() : void
    {
        self::init();

        self::doClearCaches();
    }

    public static function doClearCaches() : void
    {
        echo 'Clearing all caches...'.PHP_EOL;

        // Do this manually first, because the cache manager
        // depends on offline events being registered, and
        // which may not exist at this point.
        ClassCacheHandler::clearClassCache();

        CacheManager::getInstance()->clearAll();

        echo 'DONE.'.PHP_EOL;
    }

    public static function apiMethodIndex() : void
    {
        self::init();

        self::doApiMethodIndex();
    }

    public static function indexAdminScreens() : void
    {
        self::init();

        self::doIndexAdminScreens();
    }

    public static function doIndexAdminScreens() : void
    {
        echo 'Indexing admin screens...'.PHP_EOL;

        $indexer = new AdminScreenIndexer(AppFactory::createDriver());
        $indexer->index();

        echo sprintf(
            '- Found %s screens total (%s content screens).'.PHP_EOL,
            $indexer->countScreens(),
            $indexer->countContentScreens()
        );
    }

    public static function indexOfflineEvents() : void
    {
        self::init();

        self::doIndexOfflineEvents();
    }

    public static function doIndexOfflineEvents() : void
    {
        echo 'Indexing offline event listeners...'.PHP_EOL;

        $indexer = EventIndexer::getInstance();
        $indexer->index();

        echo sprintf(
            '- Found [%s] offline events and [%s] listeners total.'.PHP_EOL,
            $indexer->countEvents(),
            $indexer->countListeners()
        );
    }

    public static function doApiMethodIndex() : void
    {
        self::doClearCaches();

        AppFactory::createLogger()->logModeEcho();

        APIManager::getInstance()->getMethodIndex()->build();
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

        $bootstrap = __DIR__.'/../../../../tests/bootstrap.php';

        $file = FileInfo::factory($bootstrap);
        if(!$file->exists()) {
            throw new ApplicationException(
                'The bootstrap file could not be found at: '.$bootstrap,
                '',
                self::ERROR_BOOTSTRAP_NOT_FOUND
            );
        }

        require_once (string)$file;

        // Removed this, as the application's composer scripts are
        // run using the testsuite bootstrap. The bootstrap screen
        // is still relevant for applications, however.
        //Application_Bootstrap::bootClass(ComposerScriptBootstrap::class);
    }
}
