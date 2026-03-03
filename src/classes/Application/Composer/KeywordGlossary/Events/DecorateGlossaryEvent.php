<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary\Events;

use Application\Composer\KeywordGlossary\GlossarySection;
use Application\EventHandler\OfflineEvents\BaseOfflineEvent;

/**
 * Offline event fired when the keyword glossary is being decorated.
 * Listeners may contribute custom {@see GlossarySection} instances
 * which the generator will include in the rendered glossary document.
 *
 * ## Usage
 *
 * 1. Add listeners in the offline event folder named `DecorateGlossary`.
 * 2. Extend the base class {@see BaseDecorateGlossaryListener}.
 *
 * @package Application
 * @subpackage Composer
 */
class DecorateGlossaryEvent extends BaseOfflineEvent
{
    public const string EVENT_NAME = 'DecorateGlossary';

    /**
     * @var GlossarySection[]
     */
    private array $sections = array();

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    /**
     * Appends a glossary section contributed by a listener.
     *
     * @param GlossarySection $section
     * @return void
     */
    public function addSection(GlossarySection $section) : void
    {
        $this->sections[] = $section;
    }

    /**
     * Returns all glossary sections collected from listeners.
     *
     * @return GlossarySection[]
     */
    public function getSections() : array
    {
        return $this->sections;
    }
}
