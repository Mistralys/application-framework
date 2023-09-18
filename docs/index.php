<?php
/**
 * Displays the framework's documentation UI.
 *
 * NOTE: This is only as recent as the last Composer update.
 * For a more recent version, run `composer update` first,
 * or read the documentation online at {@link https://github.com/Mistralys/application-framework-docs}.
 *
 * @package Application
 * @subpackage Documentation
 * @see DocumentationHub
 */

declare(strict_types=1);

use Mistralys\AppFrameworkDocs\DocumentationHub;

if(!file_exists('../vendor/autoload.php')) {
    die('Please run composer install first.');
}

require_once '../vendor/autoload.php';

DocumentationHub::create(
    __DIR__.'/../vendor',
    './../vendor'
)->display();
