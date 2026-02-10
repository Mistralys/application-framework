<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents\Index;

use Application\EventHandler\OfflineEvents\OfflineEventListenerInterface;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use Mistralys\AppFramework\AppFramework;
use ReflectionClass;

/**
 * @method OfflineEventListenerInterface[] getAll()
 */
class ListenerClassFinder extends BaseClassLoaderCollectionMulti implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private string $logIdentifier;

    public function __construct()
    {
        $this->logIdentifier = 'OfflineEvents | ListenerClassFinder';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function serialize() : array
    {
        $list = array();
        foreach($this->getAll() as $listener) {
            $eventName = $listener->getEventName();
            if(!isset($list[$eventName])) {
                $list[$eventName] = array();
            }

            $list[$eventName][] = get_class($listener);

            $this->log(sprintf('Event [%s] | Found listener [%s].', $eventName, get_class($listener)));
        }

        // sort listener classes for each event name
        foreach($list as $eventName => $listeners) {
            sort($listeners);
            $list[$eventName] = $listeners;
        }

        ksort($list);

        return $list;
    }

    public function getByID(string $id): OfflineEventListenerInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            OfflineEventListenerInterface::class,
            parent::getByID($id)
        );
    }

    protected function createItemInstance(string $class): ?OfflineEventListenerInterface
    {
        $reflect = new ReflectionClass($class);
        if(
            $reflect->isAbstract()
            ||
            $reflect->isInterface()
        ) {
            return null;
        }

        return new $class();
    }

    public function getInstanceOfClassName(): string
    {
        return OfflineEventListenerInterface::class;
    }

    public function getClassFolders(): array
    {
        return AppFramework::getInstance()->getClassFolders();
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }
}
