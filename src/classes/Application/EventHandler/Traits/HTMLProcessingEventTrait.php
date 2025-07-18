<?php
/**
 * @package Event Handling
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\EventHandler\Traits;

use Application\Formable\Event\HTMLProcessingEventInterface;

/**
 * Trait used to implement an event that allows HTML code
 * to be accessed and modified.
 *
 * ## Usage
 *
 * 1. Extend the interface {@see HTMLProcessingEventInterface}.
 * 2. Use this trait.
 * 3. Implement the {@see self::getHTMLArgumentIndex()} method.
 *
 * @package Event Handling
 * @subpackage Traits
 */
trait HTMLProcessingEventTrait
{
    /**
     * Gets the argument index in which the HTML code is stored.
     * @return int
     */
    abstract protected function getHTMLArgumentIndex() : int;

    public function getHTML() : string
    {
        return $this->getArgumentString($this->getHTMLArgumentIndex());
    }

    /**
     * @param string $html
     * @return $this
     */
    public function setHTML(string $html) : self
    {
        return $this->setArgument($this->getHTMLArgumentIndex(), $html);
    }

    /**
     * @param string $needle
     * @param string $replacement
     * @return $this
     */
    public function replace(string $needle, string $replacement) : self
    {
        return $this->setHTML(
            str_replace(
                $needle,
                $replacement,
                $this->getHTML()
            )
        );
    }
}
