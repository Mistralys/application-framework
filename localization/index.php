<?php
/**
 * Translation UI for the localizable strings in the package.
 *
 * @package Application Utils
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
    
    $root = __DIR__;
    $vendorFolder = realpath($root.'/../vendor');
    $installFolder = realpath($root.'/../src');
    
    $autoload = realpath($vendorFolder.'/autoload.php');
    
    // we need the autoloader to be present
    if(!file_exists($autoload)) {
        die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
    }
    
    /**
     * The composer autoloader
     */
    require_once $autoload;
    
    // add the locales we wish to manage (en_UK is always present)
    \AppLocalize\Localization::addAppLocale('de_DE');
    \AppLocalize\Localization::addAppLocale('fr_FR');
    
    // has to be called last after all sources and locales have been configured
    \AppLocalize\Localization::configure($root.'/storage.json', '');
    
    \AppLocalize\Localization::addSourceFolder(
            'application',
            'Framework classes and themes',
            'Framework',
            $installFolder.'/localization',
            $installFolder
    )
    ->excludeFolders(array('.settings', 'vendor'))
    ->excludeFiles(array('min.js', 'jquery', 'uri.js', 'bootstrap', 'ckeditor', 'redactor'));
    
    // create the editor UI and start it
    $editor = \AppLocalize\Localization::createEditor();
    $editor->display();
