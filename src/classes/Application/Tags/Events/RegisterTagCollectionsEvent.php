<?php
/**
 * @package Tagging
 * @subpackage Events
 */

declare(strict_types=1);

namespace Application\Tags\Events;

use Application\EventHandler\OfflineEvents\BaseOfflineEvent;
use Application\Tags\TagCollectionRegistry;
use Application\Tags\Taggables\TagCollectionInterface;

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
class RegisterTagCollectionsEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'RegisterTagCollections';

    protected function _getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function registerTagCollection(TagCollectionInterface $collection) : void
    {
        TagCollectionRegistry::getInstance()->registerCollection($collection);
    }
}
