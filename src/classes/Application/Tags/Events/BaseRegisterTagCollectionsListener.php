<?php
/**
 * @package Tagging
 * @subpackage Events
 */

declare(strict_types=1);

namespace Application\Tags\Events;

use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use Application\Tags\Taggables\TagCollectionInterface;
use Application_EventHandler_Event;
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

    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
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
