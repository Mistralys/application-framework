<?php
/**
 * @package User Interface
 * @subpackage Admin URLs
 */

declare(strict_types=1);

namespace UI\AdminURLs;

/**
 * Interface for classes that give access to admin URLs,
 * typically for a specific entity.
 *
 * @package User Interface
 * @subpackage Admin URLs
 */
interface AdminURLsInterface
{
    /**
     * Gets the base admin URL for the entity.
     *
     * This should be a meaningful entry point for the entity,
     * typically a list, status or overview screen.
     *
     *
     * @return AdminURLInterface
     */
    public function base() : AdminURLInterface;
}
