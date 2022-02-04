<?php
/**
 * File containing the {@link Connectors} class.
 * 
 * @package Connectors
 * @see Connectors
 */

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
    public const ERROR_INVALID_CONNECTOR_TYPE = 100701;

    protected static $connectors = array();
    
   /**
    * Creates/gets the connector of the specified type. The type is
    * the name of the connector file, case-sensitive. Will throw
    * an exception if the type does not exist.
    * 
    * @param string $type
    * @return Connectors_Connector
    *
    * @throws Application_Exception_UnexpectedInstanceType
    * @see Connectors::ERROR_INVALID_CONNECTOR_TYPE
    */
    public static function createConnector(string $type) : Connectors_Connector
    {
        if(isset(self::$connectors[$type]))
        {
            return self::$connectors[$type];
        }

        $class = Connectors_Connector::class.'_'.$type;
        $connector = new $class();

        if($connector instanceof Connectors_Connector)
        {
            return $connector;
        }

        throw new Application_Exception_UnexpectedInstanceType(
            Connectors_Connector::class,
            $connector,
            self::ERROR_INVALID_CONNECTOR_TYPE
        );
    }

    public static function createDummyConnector() : Connectors_Connector_Dummy
    {
        $dummy = self::createConnector('Dummy');

        if($dummy instanceof Connectors_Connector_Dummy)
        {
            return $dummy;
        }

        throw new Application_Exception_UnexpectedInstanceType(Connectors_Connector_Dummy::class, $dummy);
    }
}
