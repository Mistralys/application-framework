<?php
/**
 * @package Event Handling
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\Formable\Event;

use Application\EventHandler\Traits\HTMLProcessingEventTrait;

/**
 * Interface for the trait {@see HTMLProcessingEventTrait}.
 *
 * @package Event Handling
 * @subpackage Traits
 * @see HTMLProcessingEventTrait
 */
interface HTMLProcessingEventInterface
{
    public function getHTML() : string;
    public function setHTML(string $html);

    /**
     * @param string $needle
     * @param string $replacement
     * @return $this
     */
    public function replace(string $needle, string $replacement) : self;
}
