<?php

declare(strict_types=1);

namespace Application\Interfaces;

use Application\Revisionable\RevisionableChangelogTrait;
use Application_Changelog_FilterCriteria;
use Application_User;

/**
 * @see RevisionableChangelogTrait
 */
interface ChangelogableInterface
{
    /**
     * @return string
     */
    public function getChangelogTable(): string;

    /**
     * Gives the item the possibility to adjust the changelog
     * query further in case additional join or where statements
     * are required.
     *
     * @param Application_Changelog_FilterCriteria $filters
     */
    public function configureChangelogFilters(Application_Changelog_FilterCriteria $filters): void;

    /**
     * Retrieves the values for the item's primary key in the
     * changelog table. Note: should include the item's revision
     * in the case of revisionables.
     *
     * @return array
     */
    public function getChangelogItemPrimary(): array;

    /**
     * Retrieves the human-readable text to sum up the
     * change made with this changelog entry.
     *
     * @param string $type
     * @param array<mixed> $data
     * @return string
     */
    public function getChangelogEntryText(string $type, array $data = array()): string;

    /**
     * Retrieves a human-readable comparison of the before
     * and after values. Must return an array with two keys,
     * or null if it is not applicable.
     *
     * <pre>
     * array(
     *     'before' => 'Old value',
     *     'after' => 'New value'
     * )
     * </pre>
     *
     * @param string $type
     * @param array<mixed> $data
     * @return array{before:string,after:string}|null
     */
    public function getChangelogEntryDiff(string $type, array $data = array()): ?array;

    /**
     * Retrieves the current entries in the changelog that
     * have to be committed to the database. This is an indexed
     * array with the following structure:
     *
     * <pre>
     * array(
     *     array(
     *         'type' => 'changelog_type',
     *         'data' => [mixed]
     *     ),
     *     ...
     * )
     *
     * @return array<int,array<string,mixed>>
     */
    public function getChangelogQueue(): array;

    /**
     * @return string[]
     */
    public function getChangelogQueueTypes(): array;

    /**
     * Retrieves the owner of the changelog entries being added.
     * Only used when adding new entries, not for existing entries
     * in the database.
     *
     * @return Application_User
     */
    public function getChangelogOwner(): Application_User;

    /**
     * Retrieves a human-readable label for the specified changelog type.
     *
     * @param string $type
     * @return string
     */
    public function getChangelogTypeLabel(string $type): string;
}
