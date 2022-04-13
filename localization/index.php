<?php
/**
 * Translation UI for the texts in the application that can be localized.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

use AppLocalize\Localization;

$installFolder = __DIR__.'/../src';
$autoload = __DIR__ . '/../vendor/autoload.php';

// we need the autoloader to be present
if (!file_exists($autoload)) {
    die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
}

/**
 * The composer autoloader
 */
require_once $autoload;

// add the locales we wish to manage (en_UK is always present)
Localization::addAppLocale('de_DE');
Localization::addAppLocale('fr_FR');

// has to be called last after all sources and locales have been configured
Localization::configure(__DIR__ . '/storage.json');

Localization::addSourceFolder(
    'application',
    'Framework classes and themes',
    'Framework',
    $installFolder . '/localization',
    $installFolder
)
    ->excludeFolders(array('.settings', 'vendor'))
    ->excludeFiles(array('min.js', 'jquery', 'uri.js', 'bootstrap', 'ckeditor', 'redactor'));

// create the editor UI and start it
$editor = Localization::createEditor();
$editor->display();
