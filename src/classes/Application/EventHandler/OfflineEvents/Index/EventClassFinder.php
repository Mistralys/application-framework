<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents\Index;

use Application\EventHandler\OfflineEvents\OfflineEventInterface;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use Mistralys\AppFramework\AppFramework;
use ReflectionClass;

/**
 * @method OfflineEventInterface[] getAll()
 */
class EventClassFinder extends BaseClassLoaderCollectionMulti implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private string $logIdentifier;

    public function __construct()
    {
        $this->logIdentifier = 'OfflineEvents | EventClassFinder';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function serialize() : array
    {
        $list = array();
        foreach($this->getAll() as $event)
        {
            $eventName = $event->getName();

            $list[$eventName] = get_class($event);

            $this->log(sprintf('Found event [%s].', $eventName));
        }

        ksort($list);

        return $list;
    }

    public function getByID(string $id): OfflineEventInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            OfflineEventInterface::class,
            parent::getByID($id)
        );
    }

    protected function createItemInstance(string $class): ?OfflineEventInterface
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
        return OfflineEventInterface::class;
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
