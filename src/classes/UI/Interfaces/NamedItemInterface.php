<?php
/**
 * @package User Interface
 * @subpackage Interfaces
 */

declare(strict_types=1);

namespace UI\Interfaces;

/**
 * Interface for elements that can be given a name.
 *
 * @package User Interface
 * @subpackage Interfaces
 */
interface NamedItemInterface
{
    /**
     * Sets the element's name, which can be used to retrieve it when used in collections.
     * @param string $name
     * @return $this
     */
    public function setName(string $name) : self;

    /**
     * @return string|null
     */
    public function getName() : ?string;
}
