<?php
/**
 * @package Tagging
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application\OfflineEvents\RegisterTagCollectionsEvent;
use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\Taggables\TaggableInterface;
use Application\Tags\Taggables\TaggableUniqueID;
use AppUtils\Collections\BaseStringPrimaryCollection;

/**
 * Registry for tag collections, mainly used to find taggable records
 * by their unique ID.
 *
 * ## Usage
 *
 * Get an instance via {@see AppFactory::createTags()} and then {@see TagCollection::createCollectionRegistry()}.
 *
 * @package Tagging
 * @subpackage Collection
 *
 * @method TagCollectionInterface getByID(string $id)
 * @method TagCollectionInterface[] getAll()
 */
class TagCollectionRegistry extends BaseStringPrimaryCollection
{
    private static ?self $instance = null;

    public static function getInstance(): self
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

    /**
     * @return void
     */
    protected function registerItems(): void
    {
        // Triggering the event is enough, as the event listeners
        // will call the registerCollection method.
        AppFactory::createOfflineEvents()->triggerEvent(RegisterTagCollectionsEvent::EVENT_NAME);
    }

    /**
     * @param TagCollectionInterface $collection
     * @return void
     * @see RegisterTagCollectionsEvent::registerTagCollection()
     */
    public function registerCollection(TagCollectionInterface $collection): void
    {
        $this->registerItem($collection);
    }

    public function uniqueIDExists(string $uniqueID): bool
    {
        return TaggableUniqueID::parse($uniqueID)
            ->taggableExists();
    }

    /**
     * @param string $uniqueID
     * @return TaggableInterface
     */
    public function getTaggableByUniqueID(string $uniqueID): TaggableInterface
    {
        return TaggableUniqueID::parse($uniqueID)
            ->requireExists()
            ->getTaggable();
    }
}

