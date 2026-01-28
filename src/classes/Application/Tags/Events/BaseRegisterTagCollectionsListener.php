<?php
/**
 * @package Tagging
 * @subpackage Events
 */

declare(strict_types=1);

namespace Application\Tags\Events;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use Application\Tags\Taggables\TagCollectionInterface;
use AppUtils\ClassHelper;

/**
 * Base class for offline listeners that register tag collections.
 *
 * @package Tagging
 * @subpackage Events
 * @see RegisterTagCollectionsEvent
 */
abstract class BaseRegisterTagCollectionsListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return RegisterTagCollectionsEvent::EVENT_NAME;
    }

    protected function handleEvent(EventInterface $event, ...$args): void
    {
        $this->handleTagRegistration(
            ClassHelper::requireObjectInstanceOf(
                RegisterTagCollectionsEvent::class,
                $event
            )
        );
    }

    protected function handleTagRegistration(RegisterTagCollectionsEvent $event) : void
    {
        foreach ($this->getCollections() as $collection) {
            $event->registerTagCollection($collection);
        }
    }

    /**
     * @return TagCollectionInterface[]
     */
    abstract protected function getCollections() : array;
}
