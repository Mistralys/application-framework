<?php
/**
 * Changelog view for developers: text-based with only the
 * dev changelog entries from the WHATSNEW.xml file.
 *
 * @package Application
 * @subpackage TestDriver
 */

    /**
     * The bootstrapper that starts the target application screen.
     * @see Application_Bootstrap
     */
    require_once __DIR__.'/bootstrap.php';
    
    Application_Bootstrap::boot('Changelog');
