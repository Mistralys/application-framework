<?php
/**
 * File containing the {@link Connectors} class.
 * 
 * @package Connectors
 * @see Connectors
 */

/**
 * The base connector class.
 * @see Connectors_Connector
 */
require_once 'Connectors/Connector.php';

/**
 * The connectors exception class
 * @see Connectors_Exception
 */
require_once 'Connectors/Exception.php';

/**
 * External API connectors manager: handles access to
 * connector classes for the available connections to 
 * external applications.
 * 
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors
{
    protected static $connectors = array();
    
   /**
    * Creates/gets the connector of the specified type. The type is
    * the name of the connector file, case sensitive. Will throw
    * an exception if the type does not exist.
    * 
    * @param string $type
    * @return Connectors_Connector
    * @throws Application_Exception
    */
    public static function createConnector($type)
    {
        if(!isset(self::$connectors[$type])) 
        {
            $class = 'Connectors_Connector_'.$type;
            Application::requireClass($class);
            self::$connectors[$type] = new $class();
        }
        
        return self::$connectors[$type];
    }

    public static function createDummyConnector() : Connectors_Connector_Dummy
    {
        return ensureType(
            Connectors_Connector_Dummy::class,
            self::createConnector('Dummy')
        );
    }
}