<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

use Application\AppFactory;
use Application\OfflineEvents\RegisterCacheLocationsEvent;
use Application_EventHandler;
use Application_EventHandler_OfflineEvents_OfflineEvent;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\ConvertHelper;
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
class CacheManager extends BaseStringPrimaryCollection
{
    public const DOCUMENTATION_URL = DocumentationPages::MANAGING_CACHE_LOCATIONS;

    private static ?self $instance = null;

    private function __construct()
    {
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
        foreach($this->triggerRegisterEvent()->getLocations() as $location) {
            $this->registerItem($location);
        }
    }

    /**
     * @return $this
     */
    public function clearAll() : self
    {
        foreach($this->getAll() as $location) {
            $location->clear();
        }

        return $this;
    }

    private function triggerRegisterEvent() : RegisterCacheLocationsEvent
    {
        $event = Application_EventHandler::createOfflineEvents()->triggerEvent(
            RegisterCacheLocationsEvent::EVENT_NAME,
            array(),
            RegisterCacheLocationsEvent::class
        );

        if($event !== null) {
            return ClassHelper::requireObjectInstanceOf(
                RegisterCacheLocationsEvent::class,
                $event->getTriggeredEvent()
            );
        }

        throw new CacheManagerException(
            'Failed to trigger the cache locations registration event.',
            sprintf(
                'No event instance was returned by [%s].',
                ConvertHelper::callback2string(array(Application_EventHandler_OfflineEvents_OfflineEvent::class, 'getTriggeredEvent'))
            ),
            CacheManagerException::ERROR_FAILED_TO_TRIGGER_REGISTRATION_EVENT
        );
    }
}
