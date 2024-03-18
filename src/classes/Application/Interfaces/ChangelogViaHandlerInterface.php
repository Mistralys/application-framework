<?php
/**
 * @package Application
 * @subpackage Changelogable
 */

declare(strict_types=1);

namespace Application\Interfaces;

use Application\Traits\ChangelogViaHandlerTrait;

/**
 * Interface for classes that use the trait {@see ChangelogViaHandlerTrait}.
 *
 * @package Application
 * @subpackage Changelogable
 *
 * @see ChangelogViaHandlerTrait
 */
interface ChangelogViaHandlerInterface extends ChangelogableInterface
{
    /**
     * @return class-string
     */
    public function getChangelogHandlerClass() : string;
}
