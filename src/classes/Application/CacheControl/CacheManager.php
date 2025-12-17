<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

use Application\AppFactory;
use Application\CacheControl\Events\RegisterCacheLocationsEvent;
use Application_EventHandler;
use Application_Traits_Loggable;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper\FolderInfo;
use Mistralys\AppFrameworkDocs\DocumentationPages;

/**
 * The cache manager is a singleton that acts as an inventory of
 * all cache locations in the application. From the temporary files
 * folder to the database, all cache locations are registered here.
 *
 * Each location is an instance of {@see CacheLocationInterface}.
 *
 * ## Usage
 *
 * Get the cache manager instance with {@see self::getInstance()} or
 * via {@see AppFactory::createCacheManager()}.
 *
 * ## Registering Cache Locations
 *
 * As each location can be wildly difference, and because applications
 * can have their own custom cache locations, locations can be
 * registered in the offline event {@see RegisterCacheLocationsEvent}.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @method CacheLocationInterface getByID(string $id)
 * @method CacheLocationInterface[] getAll()
 *
 * @see self::DOCUMENTATION_URL
 */
class CacheManager extends BaseStringPrimaryCollection implements \Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const string DOCUMENTATION_URL = DocumentationPages::MANAGING_CACHE_LOCATIONS;

    private static ?self $instance = null;
    private string $logIdentifier;

    private function __construct()
    {
        $this->logIdentifier = 'CacheManager';
    }

    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__ . '/Admin/Screens')->requireExists();
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return '';
    }

    protected function registerItems(): void
    {
        $event = $this->triggerRegisterEvent();

        if($event === null) {
            return;
        }

        foreach($event->getLocations() as $location) {
            $this->registerItem($location);
        }
    }

    /**
     * @return $this
     */
    public function clearAll() : self
    {
        $this->log('Clearing all caches...');

        foreach($this->getAll() as $location)
        {
            $this->log(
                '[%s] %s (%s bytes)',
                $location->getID(),
                $location->getLabel(),
                $location->getByteSize()
            );

            $location->clear();
        }

        return $this;
    }

    private function triggerRegisterEvent() : ?RegisterCacheLocationsEvent
    {
        $event = Application_EventHandler::createOfflineEvents()->triggerEvent(
            RegisterCacheLocationsEvent::EVENT_NAME
        )
            ->getTriggeredEvent();

        if($event === null) {
            return null;
        }

        return ClassHelper::requireObjectInstanceOf(
            RegisterCacheLocationsEvent::class,
            $event
        );
    }
}
