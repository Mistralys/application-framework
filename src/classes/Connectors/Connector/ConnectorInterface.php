<?php

declare(strict_types=1);

namespace Connectors\Connector;

use Application_Interfaces_Loggable;
use Application_Interfaces_Simulatable;
use AppUtils\ClassHelper\BaseClassHelperException;
use Connectors_Response;

/**
 * Base class for connector implementations: offers a number
 * of utility methods that can be used by the individual
 * connectors and defines the common interface that connectors
 * have to conform to.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface ConnectorInterface extends Application_Interfaces_Simulatable, Application_Interfaces_Loggable
{
    /**
     * Checks if live requests are enabled. They are enabled
     * by default but turned off in simulation mode.
     *
     * With the <code>live-requests</code> boolean request
     * parameter, they can be turned on explicitly.
     *
     * @return bool
     */
    public function isLiveRequestsEnabled(): bool;

    /**
     * Retrieves the connector's ID (name). e.g. <code>Editor</code>.
     * This is the name of the connector file without the extension
     * (case-sensitive).
     *
     * @return string
     */
    public function getID(): string;

    /**
     * Retrieves the URL to connect to.
     *
     * @return string
     */
    public function getURL(): string;

    /**
     * Retrieves the response object from the last request.
     * @return Connectors_Response|null
     */
    public function getActiveResponse(): ?Connectors_Response;

    public function requireActiveResponse(): Connectors_Response;

    /**
     * Adds a parameter to be added to the target URL
     * that the request will call. This is separate
     * from the data array provided to {@link getData()},
     * which is sent via POST.
     *
     * @param string $name
     * @param string|int|float|bool $value
     * @return ConnectorInterface
     */
    public function addParam(string $name, string|int|float|bool $value): ConnectorInterface;

    /**
     * @param bool $state
     * @return $this
     */
    public function setDebug(bool $state = true): ConnectorInterface;

    public function getLogIdentifier(): string;

    /**
     * Creates a new connector method instance, which is
     * loaded for the current connector type.
     *
     * ## Legacy class names
     *
     * For legacy methods, the class name follows this scheme:
     *
     * ```
     * Connectors_Connector_(ConnectorName)_Method_(MethodName)
     * ```
     *
     * @param string|class-string<BaseConnectorMethod> $nameOrClass
     * @param mixed ...$constructorArgs Additional arguments to pass to the method constructor.
     *                                  Note: The connector instance is always passed as the
     *                                  first argument.
     * @return BaseConnectorMethod
     * @throws BaseClassHelperException
     */
    public function createMethod(string $nameOrClass, ...$constructorArgs): BaseConnectorMethod;
}