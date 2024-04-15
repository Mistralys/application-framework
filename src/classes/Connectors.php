<?php
/**
 * @package Connectors
 */

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use Connectors\Connector\StubConnector;

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

    /**
     * @var array<string,Connectors_Connector>
     */
    protected static array $connectors = array();

    /**
     * Creates/gets the connector of the specified type. The type is
     * the name of the connector file, case-sensitive. Will throw
     * an exception if the type does not exist.
     *
     * @param string|class-string $typeOrClass Connector type ID or class name.
     * @return Connectors_Connector
     *
     * @throws BaseClassHelperException
     * @see Connectors::ERROR_INVALID_CONNECTOR_TYPE
     */
    public static function createConnector(string $typeOrClass) : Connectors_Connector
    {
        if(isset(self::$connectors[$typeOrClass]))
        {
            return self::$connectors[$typeOrClass];
        }

        if(class_exists($typeOrClass)) {
            $class = $typeOrClass;
        } else {
            $class = ClassHelper::requireResolvedClass('Connectors_Connector_' . $typeOrClass);
        }

        return ClassHelper::requireObjectInstanceOf(
            Connectors_Connector::class,
            new $class(),
            self::ERROR_INVALID_CONNECTOR_TYPE
        );
    }

    public static function createStubConnector() : StubConnector
    {
        return ClassHelper::requireObjectInstanceOf(
            StubConnector::class,
            self::createConnector(StubConnector::class)
        );
    }
}
