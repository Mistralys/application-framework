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
use Application\CacheControl\CacheManager;
use Application\Composer\KeywordGlossary\KeywordGlossaryGenerator;
use Application\Composer\ModulesOverview\ModulesOverviewGenerator;
use Application\EventHandler\OfflineEvents\Index\EventIndexer;
use Application\Exception\ApplicationException;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Microtime;

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
        self::generateOpenAPISpec();
        self::generateHtaccess();
        self::generateCSSClassesJS();
        self::updateContextGenerateDate();
        self::updateModuleDocumentation();
    }

    private static function updateModuleDocumentation() : void
    {
        self::doUpdateModuleDocumentation();
    }

    public static function doUpdateModuleDocumentation() : void
    {
        echo '- Updating module documentation...'.PHP_EOL;

        $rootFolder = FolderInfo::factory(__DIR__.'/../../../../');

        (new ModulesOverviewGenerator($rootFolder))->generate();

        $glossaryOutputPath = rtrim($rootFolder->getPath(), '/').'/docs/agents/project-manifest/module-glossary.md';

        (new KeywordGlossaryGenerator($rootFolder))->generate($glossaryOutputPath);

        echo '  DONE.'.PHP_EOL;
    }

    private static function updateContextGenerateDate() : void
    {
        echo '- Updating context generate date...'.PHP_EOL;

        FileInfo::factory(__DIR__.'/../../../../.context/generated-at.txt')
            ->putContents(Microtime::createNow()->getISODate(true));

        echo '  DONE.'.PHP_EOL;
    }

    public static function clearCaches() : void
    {
        self::init();

        self::doClearCaches();
    }

    public static function doClearCaches() : void
    {
        echo '- Clearing all caches...'.PHP_EOL;

        // Do this manually first, because the cache manager
        // depends on offline events being registered, and
        // which may not exist at this point.
        ClassCacheHandler::clearClassCache();

        CacheManager::getInstance()->clearAll();

        echo '  DONE.'.PHP_EOL;
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
        echo '- Indexing admin screens...'.PHP_EOL;

        $indexer = new AdminScreenIndexer(AppFactory::createDriver());
        $indexer->index();

        echo sprintf(
            '  Found %s screens total (%s content screens).'.PHP_EOL,
            $indexer->countScreens(),
            $indexer->countContentScreens()
        );

        echo '  DONE.'.PHP_EOL;
    }

    public static function indexOfflineEvents() : void
    {
        self::init();

        self::doIndexOfflineEvents();
    }

    public static function doIndexOfflineEvents() : void
    {
        echo '- Indexing offline event listeners...'.PHP_EOL;

        $indexer = EventIndexer::getInstance();
        $indexer->index();

        echo sprintf(
            '  Found [%s] offline events and [%s] listeners total.'.PHP_EOL,
            $indexer->countEvents(),
            $indexer->countListeners()
        );

        echo '  DONE.'.PHP_EOL;
    }

    public static function doApiMethodIndex() : void
    {
        self::doClearCaches();

        AppFactory::createLogger()->logModeEcho();

        APIManager::getInstance()->getMethodIndex()->build();
    }

    public static function generateOpenAPISpec() : void
    {
        self::init();

        self::doGenerateOpenAPISpec();
    }

    public static function doGenerateOpenAPISpec() : void
    {
        echo '- Generating OpenAPI specification...'.PHP_EOL;

        try {
            $apiDir = self::getFrameworkAPIOutputDirectory();
            $outputPath = $apiDir !== '' ? $apiDir.'/openapi.json' : '';
            $path = APIManager::getInstance()->generateOpenAPISpec($outputPath);
            echo sprintf('  Written to: %s'.PHP_EOL, $path);
        } catch (\Throwable $e) {
            error_log('OpenAPI spec generation failed: '.$e->getMessage());
            echo sprintf('  WARNING: OpenAPI spec generation failed: %s'.PHP_EOL, $e->getMessage());
        }

        echo '  DONE.'.PHP_EOL;
    }

    public static function generateHtaccess() : void
    {
        self::init();

        self::doGenerateHtaccess();
    }

    public static function doGenerateHtaccess() : void
    {
        echo '- Generating API .htaccess...'.PHP_EOL;

        try {
            $path = APIManager::getInstance()->generateHtaccess(self::getFrameworkAPIOutputDirectory());
            echo sprintf('  Written to: %s'.PHP_EOL, $path);
        } catch (\Throwable $e) {
            error_log('API .htaccess generation failed: '.$e->getMessage());
            echo sprintf('  WARNING: API .htaccess generation failed: %s'.PHP_EOL, $e->getMessage());
        }

        echo '  DONE.'.PHP_EOL;
    }

    public static function generateCSSClassesJS() : void
    {
        self::init();

        self::doGenerateCSSClassesJS();
    }

    public static function doGenerateCSSClassesJS() : void
    {
        echo '- Generating clientside CSS classes reference...'.PHP_EOL;

        new CSSClassesGenerator()->generate();

        echo '  DONE.'.PHP_EOL;
    }

    private static bool $initialized = false;

    /**
     * Returns the absolute path to the framework's test application API directory
     * when running within the framework's own GIT package, so that generated files
     * (`.htaccess`, `openapi.json`) are written there instead of the default
     * `APP_INSTALL_FOLDER/api` which resolves to `/src/api` in the test bootstrap.
     *
     * Returns an empty string when running inside an application (where the
     * `tests/application/api` folder does not exist relative to this file).
     *
     * @return string Absolute path to `tests/application/api`, or empty string.
     */
    private static function getFrameworkAPIOutputDirectory() : string
    {
        $path = __DIR__.'/../../../../tests/application/api';
        if(is_dir($path)) {
            return (string)realpath($path);
        }
        return '';
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
