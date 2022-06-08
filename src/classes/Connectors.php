<?php
/**
 * File containing the {@link Connectors} class.
 * 
 * @package Connectors
 * @see Connectors
 */

use Application\Exception\UnexpectedInstanceException;

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
    * @throws UnexpectedInstanceException
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

        throw new UnexpectedInstanceException(
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

        throw new UnexpectedInstanceException(Connectors_Connector_Dummy::class, $dummy);
    }
}
