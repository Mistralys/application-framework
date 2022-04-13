<?php
/**
 * Logs failed AJAX requests.
 *  
 * @package Application
 * @subpackage Ajax
 * @see Application_Bootstrap_Screen_AjaxError
 */

    /**
     * The bootstrapper that starts the target application screen.
     * @see Application_Bootstrap
     */
    require_once __DIR__.'/../bootstrap.php';
    
    Application_Bootstrap::boot('AjaxError');