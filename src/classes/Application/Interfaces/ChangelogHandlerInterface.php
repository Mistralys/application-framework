<?php
/**
 * @package Application
 * @subpackage Changelogables
 */

declare(strict_types=1);

namespace Application\Interfaces;

use Application\Changelog\BaseChangelogHandler;
use Application_Changelog_FilterCriteria;

/**
 * Interface for changelog handler classes. See the
 * {@see BaseChangelogHandler} class for a base implementation.
 *
 * @package Application
 * @subpackage Changelogables
 *
 * @see BaseChangelogHandler
 */
interface ChangelogHandlerInterface
{
    public function getChangelogable() : ChangelogableInterface;

    public function configureFilters(Application_Changelog_FilterCriteria $filters) : void;

    /**
     * @param string $type
     * @param array<mixed> $data
     * @return string
     */
    public function getEntryText(string $type, array $data) : string;

    /**
     * Retrieves a human-readable comparison of the before
     * and after values. Must return an array with two keys,
     * or null if it is not applicable.
     *
     *  <pre>
     *  array(
     *      'before' => 'Old value',
     *      'after' => 'New value'
     *  )
     *  </pre>
     *
     * @param string $type
     * @param array<mixed> $data
     * @return array{before:string,after:string}|null
     */
    public function getEntryDiff(string $type, array $data = array()): ?array;

    public function getTypeLabel(string $type): string;

    /**
     * @return array<string,string> Changelog type => Human-readable label pairs.
     */
    public static function getTypeLabels() : array;
}
