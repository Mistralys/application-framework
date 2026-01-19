<?php
/**
 * @package Connectors
 */

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use Connectors\Connector\StubConnector;
use Mistralys\VariableHasher\VariableHasher;

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
    public const int ERROR_INVALID_CONNECTOR_TYPE = 100701;

    /**
     * @var array<string,Connectors_Connector>
     */
    protected static array $connectors = array();

    /**
     * Creates/gets the connector of the specified type. The type is
     * the name of the connector file, case-sensitive. Will throw
     * an exception if the type does not exist.
     *
     * Connector instances are cached, so multiple calls with the same
     * arguments will return the same instance.
     *
     * @param string|class-string<Connectors_Connector> $typeOrClass Connector type ID or class name.
     * @param mixed ...$constructorArguments Optional arguments to pass to the connector's constructor.
     * @return Connectors_Connector
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @see Connectors::ERROR_INVALID_CONNECTOR_TYPE
     */
    public static function createConnector(string $typeOrClass, ...$constructorArguments) : Connectors_Connector
    {
        $cacheKey = VariableHasher::create($typeOrClass, $constructorArguments)->getHash();

        if(isset(self::$connectors[$cacheKey]))
        {
            return self::$connectors[$cacheKey];
        }

        if(class_exists($typeOrClass)) {
            $class = $typeOrClass;
        } else {
            $class = ClassHelper::requireResolvedClass('Connectors_Connector_' . $typeOrClass);
        }

        $instance = ClassHelper::requireObjectInstanceOf(
            Connectors_Connector::class,
            new $class(...$constructorArguments),
            self::ERROR_INVALID_CONNECTOR_TYPE
        );

        self::$connectors[$cacheKey] = $instance;

        return $instance;
    }

    public static function createStubConnector() : StubConnector
    {
        return ClassHelper::requireObjectInstanceOf(
            StubConnector::class,
            self::createConnector(StubConnector::class)
        );
    }

    /**
     * @param string|class-string<Connectors_Connector> $connectorID
     * @return bool
     */
    public static function connectorExists(string $connectorID) : bool
    {
        if(class_exists($connectorID) && is_a($connectorID, Connectors_Connector::class, true)) {
            return true;
        }

        $class = ClassHelper::resolveClassName('Connectors_Connector_' . $connectorID);

        return $class !== null && class_exists($class) && is_a($class, Connectors_Connector::class, true);
    }
}
