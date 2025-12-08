<?php
/**
 * @package Application
 * @subpackage AppFactory
 * @see \Application\AppFactory
 */

declare(strict_types=1);

namespace Application;

use Application;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory\AppFactoryException;
use Application\AppFactory\ClassCacheHandler;
use Application\CacheControl\CacheManager;
use Application\Campaigns\CampaignCollection;
use Application\DeploymentRegistry\DeploymentRegistry;
use Application\Driver\DevChangelog;
use Application\Driver\DriverException;
use Application\Driver\DriverSettings;
use Application\Driver\VersionInfo;
use Application\Media\Collection\MediaCollection;
use Application\NewsCentral\NewsCollection;
use Application\SourceFolders\SourceFoldersManager;
use Application\SystemMails\SystemMailer;
use Application\Tags\TagCollection;
use Application\TimeTracker\TimeTrackerCollection;
use Application_Countries;
use Application_DBDumps;
use Application_Driver;
use Application_ErrorLog;
use Application_EventHandler;
use Application_EventHandler_OfflineEvents;
use Application_Logger;
use Application_LookupItems;
use Application_Maintenance;
use Application_Media;
use Application_Messagelogs;
use Application_Ratings;
use Application_Request;
use Application_RequestLog;
use Application_Session;
use Application_Sets;
use Application_Uploads;
use Application_User;
use Application_Users;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper\FolderInfo;
use DBHelper;
use DeeplHelper;
use UI;
use UI_Themes_Theme;
use function AppUtils\parseVariable;

/**
 * Centralized factory helper class: Easy access to most of the
 * collections and utilities that the framework offers.
 *
 * @package Application
 * @subpackage AppFactory
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AppFactory
{
    // region: A - Factory methods

    /**
     * Get the manager instance that handles folders from which classes
     * are loaded dynamically.
     *
     * @return SourceFoldersManager
     */
    public static function createFoldersManager() : SourceFoldersManager
    {
        return SourceFoldersManager::getInstance();
    }

    public static function createRequest() : Application_Request
    {
        return Application_Request::getInstance();
    }

    public static function createUploads() : Application_Uploads
    {
        return Application_Uploads::getInstance();
    }

    public static function createNews() : NewsCollection
    {
        return self::createClassInstance(NewsCollection::class);
    }

    public static function createMedia() : Application_Media
    {
        return Application_Media::getInstance();
    }

    public static function createMediaCollection() : MediaCollection
    {
        return self::createClassInstance(MediaCollection::class);
    }

    public static function createLogger() : Application_Logger
    {
        return self::createClassInstance(Application_Logger::class);
    }

    public static function createAppSets() : Application_Sets
    {
        return Application_Sets::getInstance();
    }

    public static function createDBDumps() : Application_DBDumps
    {
        return self::createClassInstance(Application_DBDumps::class, Application_Driver::getInstance());
    }

    public static function createTheme() : UI_Themes_Theme
    {
        return self::createUI()->getTheme();
    }

    public static function createDriver() : Application_Driver
    {
        return Application_Driver::getInstance();
    }

    public static function createUI() : UI
    {
        return UI::getInstance();
    }

    public static function createSession() : Application_Session
    {
        return Application::getSession();
    }

    public static function createCountries() : Application_Countries
    {
        return Application_Countries::getInstance();
    }

    public static function createLanguages() : Languages
    {
        return Languages::getInstance();
    }

    public static function createLocales() : Locales
    {
        return Locales::getInstance();
    }

    public static function createDeeplHelper() : DeeplHelper
    {
        return self::createClassInstance(DeeplHelper::class);
    }

    public static function createDeploymentRegistry() : DeploymentRegistry
    {
        return self::createClassInstance(DeploymentRegistry::class);
    }

    public static function createMessageLog() : Application_Messagelogs
    {
        return self::createClassInstance(Application_Messagelogs::class);
    }

    public static function createMaintenance() : Application_Maintenance
    {
        return self::createClassInstance(Application_Maintenance::class, Application_Driver::getInstance());
    }

    /**
     * Creates a new instance of the API to access the information
     * from the WHATSNEW.xml file.
     *
     * @return WhatsNew
     * @throws AppFactoryException
     */
    public static function createWhatsNew() : WhatsNew
    {
        return self::createClassInstance(WhatsNew::class, APP_ROOT . '/WHATSNEW.xml');
    }

    public static function createTags() : TagCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            TagCollection::class,
            DBHelper::createCollection(TagCollection::class)
        );
    }

    public static function createVersionInfo() : VersionInfo
    {
        return VersionInfo::getInstance();
    }

    public static function createUser() : Application_User
    {
        return Application::getUser();
    }

    public static function createCampaigns() : CampaignCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            CampaignCollection::class,
            DBHelper::createCollection(CampaignCollection::class)
        );
    }

    public function createDriverSettings() : DriverSettings
    {
        return Application_Driver::createSettings();
    }

    public static function createRequestLog() : Application_RequestLog
    {
        return self::createClassInstance(Application_RequestLog::class);
    }

    public static function createErrorLog() : Application_ErrorLog
    {
        return self::createClassInstance(Application_ErrorLog::class);
    }

    /**
     * @return Application_Users
     * @throws AppFactoryException
     */
    public static function createUsers() : Application_Users
    {
        return self::createClassInstance(Application_Users::class);
    }

    /**
     * Creates/returns the instance of the application ratings,
     * which are used to handle user ratings of application screens.
     *
     * @return Application_Ratings
     * @throws AppFactoryException
     */
    public static function createRatings() : Application_Ratings
    {
        return self::createClassInstance(Application_Ratings::class);
    }

    /**
     * Retrieves the lookup items manager: this provides access to
     * all items that can be searched for using the lookup dialog.
     *
     * @return Application_LookupItems
     * @throws AppFactoryException
     * @throws DriverException
     */
    public static function createLookupItems() : Application_LookupItems
    {
        return self::createClassInstance(Application_LookupItems::class, Application_Driver::getInstance());
    }

    public static function createOfflineEvents() : Application_EventHandler_OfflineEvents
    {
        return Application_EventHandler::createOfflineEvents();
    }

    public static function createSystemMailer() : SystemMailer
    {
        return self::createClassInstance(SystemMailer::class);
    }

    /**
     * Creates a new instance of the developer changelog manager.
     *
     * @return DevChangelog
     * @throws AppFactoryException
     */
    public static function createDevChangelog() : DevChangelog
    {
        return self::createClassInstance(DevChangelog::class);
    }

    /**
     * Creates / gets the global cache manager instance used
     * to manage all cache locations in the application.
     *
     * @return CacheManager
     */
    public static function createCacheManager() : CacheManager
    {
        return CacheManager::getInstance();
    }

    public static function createTimeTracker() : TimeTrackerCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            TimeTrackerCollection::class,
            DBHelper::createCollection(TimeTrackerCollection::class)
        );
    }

    public static function createAPIClients() : APIClientsCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            APIClientsCollection::class,
            DBHelper::createCollection(APIClientsCollection::class)
        );
    }

    // endregion

    // region: X - Support methods

    public const int ERROR_INVALID_INSTANCE_CLASS = 128401;

    /**
     * @var array<string,object>
     */
    private static array $instances = array();

    /**
     * Creates an instance of a generic collection, like
     * a DBHelper collection, and returns it. Ensures that
     * only a singleton is returned every time.
     *
     * @template CLASS
     * @param class-string<CLASS> $className
     * @param mixed $parameters Any parameters the collection may need to be instantiated
     * @return CLASS
     * @throws AppFactoryException
     */
    protected static function createClassInstance(string $className, ...$parameters)
    {
        if (!isset(self::$instances[$className]))
        {
            self::$instances[$className] = new $className(...$parameters);
        }

        try
        {
            return ClassHelper::requireObjectInstanceOf(
                $className,
                self::$instances[$className]
            );
        }
        catch (BaseClassHelperException $e)
        {
            throw new AppFactoryException(
                'Instantiated object is not of the expected class.',
                sprintf(
                    'Tried creating collection of class [%s], given: [%s].',
                    $className,
                    parseVariable(self::$instances[$className])->enableType()->toString()
                ),
                self::ERROR_INVALID_INSTANCE_CLASS,
                $e
            );
        }
    }

    /**
     * Uses the class helper to find classes in the target folder.
     * The results are cached to avoid unnecessary file system
     * accesses. The cache uses the application version as a key, so
     * it is automatically invalidated when the application is updated.
     *
     * > NOTE: The cache is automatically disabled in development mode.
     *
     * @param FolderInfo $folder
     * @param bool $recursive
     * @param string|null $baseClass
     * @return class-string[]
     *
     * @see ClassCacheHandler
     */
    public static function findClassesInFolder(FolderInfo $folder, bool $recursive=false, ?string $baseClass=null) : array
    {
        return ClassCacheHandler::findClassesInFolder($folder, $recursive, $baseClass);
    }

    // endregion
}
