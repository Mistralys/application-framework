<?php
/**
 * @package Tagging
 * @subpackage Events
 */

declare(strict_types=1);

namespace Application\OfflineEvents;

use Application\Tags\Events\BaseRegisterTagCollectionsListener;
use Application\Tags\TagCollectionRegistry;
use Application\Tags\Taggables\TagCollectionInterface;
use Application_EventHandler_Event;

/**
 * This event is triggered when the available tag collections
 * must be collected.
 *
 * ## Usage
 *
 * 1. Add listeners in the folder {@see self::EVENT_NAME} in the offline event folder.
 * 2. Extend the base class {@see BaseRegisterTagCollectionsListener}.
 *
 * @package Tagging
 * @subpackage Events
 */
class RegisterTagCollectionsEvent extends Application_EventHandler_Event
{
    public const EVENT_NAME = 'RegisterTagCollections';

    public function registerTagCollection(TagCollectionInterface $collection) : void
    {
        TagCollectionRegistry::getInstance()->registerCollection($collection);
    }
}
