<?php
/**
 * @package Connectors
 */

declare(strict_types=1);

namespace Connectors\Connector;

use Application_Request;
use Application_Traits_Loggable;
use Application_Traits_Simulatable;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use Connectors_Request;
use Connectors_Request_Method;
use Connectors_Request_URL;
use Connectors_Response;

abstract class BaseConnector implements ConnectorInterface
{
    use Application_Traits_Simulatable;
    use Application_Traits_Loggable;

    protected ?string $cachedID = null;

    /**
     * @var array<string,string>
     */
    protected array $params = array();
    protected bool $debug = false;
    protected ?Connectors_Response $activeResponse;

    public function __construct()
    {
        $this->checkRequirements();
        $this->init();
    }

    /**
     * Overridable in child classes.
     */
    protected function init(): void
    {

    }

    public function isLiveRequestsEnabled(): bool
    {
        if (!$this->isSimulationEnabled()) {
            return true;
        }

        return Application_Request::getInstance()->getBool('live-requests');
    }

    public function getID(): string
    {
        if (!isset($this->cachedID)) {
            $this->cachedID = getClassTypeName($this);
        }

        return $this->cachedID;
    }

    // region: Abstract methods

    /**
     * Check that all requirements for requests are met.
     *
     * @throws ConnectorException
     */
    abstract protected function checkRequirements(): void;

    abstract public function getURL(): string;

    // endregion

    /**
     * Creates a new request with the possibility to add parameters
     * that are added as a query string. Expects the endpoint to
     * accept a method parameter for the method to call.
     *
     * @param string $method The endpoint method to call, added as a GET parameter in the request URL
     * @param array<int|string,mixed> $postData The data that will be sent along via POST
     * @param array<int|string,mixed> $getData GET data to append to the URL
     * @return Connectors_Request_Method
     */
    protected function createMethodRequest(string $method, array $postData = array(), array $getData = array()): Connectors_Request_Method
    {
        return new Connectors_Request_Method($this, $this->getURL(), $method, $postData, $getData);
    }

    /**
     * Creates a new request to a specific target url path.
     *
     * @param string $url
     * @param array<int|string,mixed> $postData
     * @param array<int|string,mixed> $getData
     * @return Connectors_Request_URL
     */
    protected function createURLRequest(string $url, array $postData = array(), array $getData = array()): Connectors_Request_URL
    {
        return new Connectors_Request_URL($this, $url, $postData, $getData);
    }

    /**
     * Generic utility method to directly retrieve the results of
     * a method request.
     *
     * @param string $method
     * @param array<int|string,mixed> $postData
     * @return array<int|string,mixed>|bool
     */
    protected function getMethodData(string $method, array $postData = array()): array|bool
    {
        return $this->fetchResponse($this->createMethodRequest($method, $postData, $this->params));
    }

    /**
     * @param string $url
     * @param array<int|string,mixed> $postData
     * @return array<int|string,mixed>|false
     */
    protected function getURLData(string $url, array $postData = array()): array|false
    {
        return $this->fetchResponse($this->createURLRequest($url, $postData, $this->params));
    }

    /**
     * @param Connectors_Request $request
     * @return array<int|string,mixed>|false
     */
    protected function fetchResponse(Connectors_Request $request): array|false
    {
        $this->activeResponse = $request->getData();

        if (!$this->activeResponse->isError()) {
            return $this->activeResponse->getData();
        }

        return false;
    }

    public function getActiveResponse(): ?Connectors_Response
    {
        return $this->activeResponse;
    }

    public function requireActiveResponse(): Connectors_Response
    {
        if (isset($this->activeResponse)) {
            return $this->activeResponse;
        }

        throw new ConnectorException(
            $this,
            'No active response available.',
            'Cannot get active response, none has been stored.',
            ConnectorException::ERROR_NO_ACTIVE_RESPONSE_AVAILABLE
        );
    }

    public function addParam(string $name, string|int|float|bool $value): ConnectorInterface
    {
        if (is_bool($value)) {
            $value = ConvertHelper::boolStrict2string($value);
        }

        $this->params[$name] = (string)$value;
        return $this;
    }

    public function setDebug(bool $state = true): ConnectorInterface
    {
        $this->debug = $state;
        return $this;
    }

    public function getLogIdentifier(): string
    {
        return 'Connector [' . $this->getID() . ']';
    }

    public function createMethod(string $nameOrClass, ...$constructorArgs): BaseConnectorMethod
    {
        if (class_exists($nameOrClass)) {
            $class = $nameOrClass;
        } else {
            $class = sprintf(
                'Connectors_Connector_%s_Method_%s',
                $this->getID(),
                $nameOrClass
            );
        }

        return ClassHelper::requireObjectInstanceOf(
            BaseConnectorMethod::class,
            new $class($this, ...$constructorArgs)
        );
    }
}
