<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary\Events;

use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use AppUtils\ClassHelper;

/**
 * Base class for offline listeners that contribute sections to the
 * keyword glossary via {@see DecorateGlossaryEvent}.
 *
 * ## Usage
 *
 * Extend this class and implement {@see handleGlossaryDecoration()} to
 * add one or more {@see \Application\Composer\KeywordGlossary\GlossarySection}
 * instances to the event.
 *
 * @package Application
 * @subpackage Composer
 * @see DecorateGlossaryEvent
 */
abstract class BaseDecorateGlossaryListener extends BaseOfflineListener
{
    public function getEventName() : string
    {
        return DecorateGlossaryEvent::EVENT_NAME;
    }

    protected function handleEvent(EventInterface $event, mixed ...$args) : void
    {
        $this->handleGlossaryDecoration(
            ClassHelper::requireObjectInstanceOf(
                DecorateGlossaryEvent::class,
                $event
            )
        );
    }

    /**
     * Implement this method to add sections to the glossary.
     *
     * @param DecorateGlossaryEvent $event
     * @return void
     */
    abstract protected function handleGlossaryDecoration(DecorateGlossaryEvent $event) : void;
}
