<?php
/**
 * Ajax dispatcher: determines the ajax method to run
 * and displays the result in the requested format.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Bootstrap_Screen_Ajax
 */

    /**
     * The bootstrapper that starts the target application screen.
     * @see Application_Bootstrap
     */
    require_once __DIR__.'/../bootstrap.php';

    Application_Bootstrap::boot('Ajax');