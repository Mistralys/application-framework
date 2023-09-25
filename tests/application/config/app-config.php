<?php
/**
 * Application-specific static configuration settings:
 * These are all settings that do not change between
 * servers (those should be added to the config-local.php).
 *
 * NOTE: This is now typically handled by the configuration
 * helper classes, see {@see \TestDriver\EnvironmentsConfig}.
 *
 * @package TestDriver
 * @subpackage Config
 */

declare(strict_types=1);

use Application\Environments;
use AppUtils\FileHelper\FolderInfo;
use TestDriver\EnvironmentsConfig;

if(!function_exists('boot_define')) {
    die('May not be accessed directly.');
}

try
{
    (new EnvironmentsConfig(FolderInfo::factory(__DIR__)))
        ->detect();
}
catch (Throwable $e)
{
    Environments::displayException($e);
}
