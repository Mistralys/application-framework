<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

use Application\AppFactory;
use Application\CacheControl\Events\RegisterCacheLocationsEvent;
use Application\EventHandler\EventManager;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseStringPrimaryCollection;
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
class CacheManager extends BaseStringPrimaryCollection implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const string DOCUMENTATION_URL = DocumentationPages::MANAGING_CACHE_LOCATIONS;

    private static ?self $instance = null;
    private string $logIdentifier;

    private function __construct()
    {
        $this->logIdentifier = 'CacheManager';
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
     * Clears all registered cache locations.
     *
     * Plain locations are cleared via {@see CacheLocationInterface::clear()};
     * {@see RegeneratingCacheLocationInterface} locations are rebuilt in
     * place instead, once every plain location has been cleared.
     *
     * @return $this
     * @throws CacheManagerException {@see CacheManagerException::ERROR_REGENERATION_FAILED}
     */
    public function clearAll() : self
    {
        $this->log('Clearing all caches...');

        return $this->clearLocations($this->getAll());
    }

    /**
     * Clears a single cache location.
     *
     * If the location implements {@see RegeneratingCacheLocationInterface},
     * it is regenerated in place instead of being cleared.
     *
     * @param CacheLocationInterface $location
     * @return $this
     * @throws CacheManagerException {@see CacheManagerException::ERROR_REGENERATION_FAILED}
     */
    public function clearLocation(CacheLocationInterface $location) : self
    {
        return $this->clearLocations(array($location));
    }

    /**
     * Clears a list of cache locations, in two phases:
     *
     * 1. Every plain location is cleared via {@see CacheLocationInterface::clear()}.
     * 2. Every {@see RegeneratingCacheLocationInterface} location is then
     *    regenerated in place via {@see RegeneratingCacheLocationInterface::regenerate()}.
     *
     * Regeneration failures do not stop the remaining locations from being
     * processed: all failures are collected, and a single wrapped exception
     * is thrown once every location has had a chance to run, naming the
     * failed locations and chaining the first encountered throwable.
     *
     * @param CacheLocationInterface[] $locations
     * @return $this
     * @throws CacheManagerException {@see CacheManagerException::ERROR_REGENERATION_FAILED}
     */
    public function clearLocations(array $locations) : self
    {
        $regenerating = array();

        foreach($locations as $location)
        {
            if($location instanceof RegeneratingCacheLocationInterface) {
                $regenerating[] = $location;
                continue;
            }

            $this->log(
                '[%s] %s (%s bytes) | Clearing.',
                $location->getID(),
                $location->getLabel(),
                $location->getByteSize()
            );

            $location->clear();
        }

        /**
         * @var array<string,\Throwable> $failures Location ID => original throwable.
         */
        $failures = array();

        foreach($regenerating as $location)
        {
            $this->log(
                '[%s] %s (%s bytes) | Regenerating.',
                $location->getID(),
                $location->getLabel(),
                $location->getByteSize()
            );

            try
            {
                $location->regenerate();
            }
            catch(\Throwable $e)
            {
                $failures[$location->getID()] = $e;
            }
        }

        if(!empty($failures))
        {
            $ids = array_keys($failures);
            $first = $failures[$ids[0]];

            throw new CacheManagerException(
                'Failed to regenerate one or more cache locations',
                sprintf(
                    'The following cache location(s) failed to regenerate: [%s]. '.PHP_EOL.
                    'See the chained exception for the first failure encountered.',
                    implode(', ', $ids)
                ),
                CacheManagerException::ERROR_REGENERATION_FAILED,
                $first
            );
        }

        return $this;
    }

    private function triggerRegisterEvent() : ?RegisterCacheLocationsEvent
    {
        $event = EventManager::createOfflineEvents()->triggerEvent(
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
