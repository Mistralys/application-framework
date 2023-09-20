<?php
/**
 * Main configuration file for the application framework
 * test suite.
 *
 * @package TestDriver
 * @subpackage Config
 */

declare(strict_types=1);

use AppUtils\FileHelper\FolderInfo;
use TestDriver\EnvironmentsConfig;

(new EnvironmentsConfig(FolderInfo::factory(__DIR__)))
    ->detect();
