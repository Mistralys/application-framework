<?php
/**
 * Test suite database configuration file template. Rename this
 * to test-db-config.php and edit the settings to enable the 
 * testsuite. Use the bundled SQL files to initialize the database.
 * 
 * @package TestDriver
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

   /**
    * The host for the testsuite database
    * @var string
    */
    define('TESTSUITE_DB_HOST', '127.0.0.1');
    
   /**
    * The name of the database to use for the testsuite
    * @var string
    */
    define('TESTSUITE_DB_NAME', 'app_framework_testsuite');
    
   /**
    * The username to access the database
    * @var string
    */
    define('TESTSUITE_DB_USER', 'root');
    
   /**
    * The password for the specified user.
    * @var string
    */
    define('TESTSUITE_DB_PASSWORD', '');