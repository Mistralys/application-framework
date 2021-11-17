<?php
/**
 * Main bootstrapper used to set up the specialized 
 * Application Test suite environment.
 * 
 * @package TestDriver
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Bootstrap_Screen_TestsSuite
 */

    /**
     * The application root folder (this file's location)
     * @var string
     */
    const APP_ROOT = __DIR__.'/application';

    const TESTS_ROOT = __DIR__;

    /**
     * The folder in which the Application libraries
     * are installed. This is the default using a
     * composer structure.
     *
     * @var string
     */
    const APP_INSTALL_FOLDER = __DIR__.'/../src';

    const APP_VENDOR_PATH = __DIR__.'/../vendor';

    /**
     * Specifies that this is the framework's own tests suite.
     * It is needed to determine the location of the tests
     * folder, which is different from where it is located in
     * the applications.
     */
    const APP_FRAMEWORK_TESTS = true;

    /**
     * The bootstrapper class that configures the
     * application environment.
     * @see Application_Bootstrap
     */
    require_once APP_INSTALL_FOLDER.'/classes/Application/Bootstrap.php';
    
    // The initialization includes the local configuration files,
    // and defines all global application settings.
    
    try
    {
        /* @see Application_Bootstrap_Screen_TestsSuite */

        Application_Bootstrap::init();
        Application_Bootstrap::boot('TestsSuite');
    }
    catch(Exception $e)
    {
        testsuite_dumpException($e);
        exit;
    }
    
    
   /**
    * Used to display an exception's details before the framework
    * error handling is loaded.
    * 
    * @param Exception $e
    */
    function testsuite_dumpException($e)
    {
        $trace = $e->getTrace();
        
        $root = str_replace('\\', '/', realpath(__DIR__.'/../'));
        
        $cnt = 0;
        
        echo PHP_EOL.
        '--------------------------------------------------'.PHP_EOL.
        'APP FRAMEWORK TESTSUITE EXCEPTION'.PHP_EOL.
        '--------------------------------------------------'.PHP_EOL.
        PHP_EOL.
        'Message: '.$e->getMessage().PHP_EOL.
        'Code: '.$e->getCode().PHP_EOL.
        PHP_EOL;
        
        foreach($trace as $entry)
        {
            $cnt++;
            
            $class = '';
            if(isset($entry['class'])) {
                $class = $entry['class'].'::';
            }
            
            $file = str_replace('\\', '/', $entry['file']);
            $file = str_replace($root, '', $file);
            $file = ltrim($file, '/');
            
            $arguments = array();
            if(isset($entry['args'])) {
                foreach($entry['args'] as $arg)
                {
                    $arguments[] = gettype($arg).':'.testsuite_var2string($arg);
                }
            }
            
            echo '#'.sprintf('%03d', $cnt).'  '.$file.':'.$entry['line'].PHP_EOL.
            '      '.$class.$entry['function'].'()'.PHP_EOL;
            
            if(!empty($arguments)) {
                echo
                '      Arguments:'.PHP_EOL.
                '          - '.implode(PHP_EOL.'          - ', $arguments).PHP_EOL;
            }
            
            echo PHP_EOL;
        }
    }

   /**
    * Converts a variable to a string representation according to
    * the data type.
    * 
    * @param mixed $arg
    * @return string
    */
    function testsuite_var2string($arg)
    {
        $type = gettype($arg);
        $maxStrlen = 180;
                
        switch($type) 
        {
            case 'boolean':
                if($arg) {
                    return 'true';
                } 
                
                return 'false';
                
            case 'string':
                $argument = $arg;
                $length = strlen($arg);
                if($length > $maxStrlen) {
                    $argument = substr($argument, 0, $maxStrlen).'[...]';
                }
                
                return '"'.$argument.'"';
                
            case 'object':
                return get_class($arg);
                
            case 'array':
                $text = '';
                foreach($arg as $key => $val) {
                    $text .= "'".$key."' => ".testsuite_var2string($val);
                }
                return $text;
        }
        
        return '';
    }