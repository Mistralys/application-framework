<?php
/**
 * @package Maintenance
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\Updaters;

use AppUtils\Interfaces\StringableInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for updaters (maintenance scripts).
 *
 * An updater is a script that performs maintenance tasks
 * on the application, such as database schema updates,
 * data migrations, or other system modifications.
 *
 * A base implementation is provided by {@see BaseUpdater}.
 *
 * @package Maintenance
 * @subpackage Core
 */
interface UpdaterInterface extends StringPrimaryRecordInterface
{
    public function start(): string;

    public function getLabel(): string;

    public function getCategory(): string;

    public function getDescription() : string;

    /**
     * @return string|string[]
     */
    public function getValidVersions(): string|array;
    public function hasSpecificVersion(string $version) : bool;

    /**
     * @param array<string,string|int|float|bool|StringableInterface|NULL> $params
     * @return string
     */
    public function buildURL(array $params = array()): string;

    /**
     * Whether this updater script is currently enabled.
     * @return bool
     * @see UpdatersCollection::isEnabled()
     */
    public function isEnabled(): bool;

    public function getListLabel(): string;
}
