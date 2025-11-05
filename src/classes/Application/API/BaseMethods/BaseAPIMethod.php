<?php

declare(strict_types=1);

namespace Application\API\BaseMethods;

use Application;
use Application\API\APIException;
use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\APIResponseDataException;
use Application\API\ErrorResponse;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\APIParamManager;
use Application\API\Parameters\ParamTypeSelector;
use Application\API\Parameters\Reserved\APIMethodParameter;
use Application\API\Parameters\Reserved\APIVersionParameter;
use Application\API\Parameters\Validation\ParamValidationResults;
use Application\API\Response\JSONInfoSerializer;
use Application\API\ResponsePayload;
use Application\API\Traits\JSONRequestInterface;
use Application_CORS;
use Application_Driver;
use Application_Exception;
use Application_Interfaces_Loggable;
use Application_Request;
use Application_Traits_Loggable;
use AppUtils\ArrayDataCollection;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Microtime;
use Throwable;
use UI\AdminURLs\AdminURLInterface;

abstract class BaseAPIMethod implements APIMethodInterface, Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    protected Application_Driver $driver;
    protected Application_Request $request;
    protected APIManager $api;
    protected ?Application_CORS $CORS = null;
    private bool $return = false;
    protected ?Microtime $time = null;
    private string $logIdentifier;

    public function __construct(APIManager $api)
    {
        $this->api = $api;
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();
        $this->logIdentifier = sprintf('APIMethod [%s]', $this->getMethodName());

        $this->initReservedParams();
        $this->init();
    }

    final public function getID(): string
    {
        return $this->getMethodName();
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    final public function getRequestTime() : ?Microtime
    {
        return $this->time;
    }

    final public function getDocumentationURL(): AdminURLInterface
    {
        return $this->api->adminURL()->methodDocumentation($this);
    }

    final public function process(): never
    {
        $this->return = false; // In case processReturn() was called before.

        $this->_process();

        Application::exit(sprintf('API Method [%s] has finished.', $this->getID()));
    }

    final public function processReturn(): ResponsePayload|ErrorResponsePayload
    {
        $this->return = true;

        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = $this->getMethodName();

        try {
            $this->_process();
        } catch (APIResponseDataException $e) {
            return $this->prepareResponse($e->getResponseData());
        }

        throw new APIException(
            'API method did not return data',
            sprintf(
                'The API method [%1$s] did not throw the return exception as expected.',
                $this->getID()
            )
        );
    }

    /**
     * @param ResponsePayload|ErrorResponsePayload|array<int|string,mixed> $response
     * @return ResponsePayload|ErrorResponsePayload
     */
    private function prepareResponse(ResponsePayload|ErrorResponsePayload|array $response) : ResponsePayload|ErrorResponsePayload
    {
        if($response instanceof ErrorResponsePayload) {
            return $response;
        }

        $class = $this->getResponseClass();

        if(is_array($response)) {
            return new ResponsePayload($this, $response);
        }

        if($class === ResponsePayload::class) {
            return $response;
        }

        return new $class($this, $response->getData());
    }

    /**
     * @return class-string<ResponsePayload>
     */
    protected function getResponseClass() : string
    {
        return ResponsePayload::class;
    }

    /**
     * @return void
     * @throws APIResponseDataException When in return mode - see class description
     * @throws Application_Exception
     */
    private function _process(): void
    {
        $this->time = Microtime::createNow();

        $this->validate();

        $version = $this->getActiveVersion();

        try {
            $this->collectRequestData($version);
        } catch (Throwable $e) {
            if($e instanceof APIResponseDataException) {
                throw $e;
            }

            $this->errorResponse(APIMethodInterface::ERROR_REQUEST_DATA_EXCEPTION)
                ->makeInternalServerError()
                ->setErrorMessage('Failed collecting request data: %s', $e->getMessage())
                ->send();
        }

        $response = ArrayDataCollection::create();

        try {
            $this->collectResponseData($response, $version);
        } catch (Throwable $e) {
            if($e instanceof APIResponseDataException) {
                throw $e;
            }

            $this->errorResponse(APIMethodInterface::ERROR_RESPONSE_DATA_EXCEPTION)
                ->makeInternalServerError()
                ->setErrorMessage('Failed collecting response data: %s', $e->getMessage())
                ->send();
        }

        $this->sendSuccessResponse($response);
    }

    /**
     * Used to give a method the opportunity to configure request
     * parameters before it is processed. Note: use the
     * {@see self::addParam()} method to add parameters.
     */
    abstract protected function init() : void;

    private ?APIParamManager $paramManager = null;

    final public function manageParams() : APIParamManager
    {
        if(!isset($this->paramManager)) {
            $this->paramManager = new APIParamManager($this);
        }

        return $this->paramManager;
    }

    private ?string $selectedVersion = null;

    public function selectVersion(string $version) : self
    {
        if(!in_array($version, $this->getVersions())) {
            throw new APIException(
                'Invalid API version selected',
                sprintf(
                    'The API version [%1$s] is not valid for method [%2$s]. '.PHP_EOL.
                    'Available versions are: [%3$s].',
                    $version,
                    $this->getID(),
                    implode(', ', $this->getVersions())
                ),
                APIException::ERROR_INVALID_API_VERSION
            );
        }

        $this->selectedVersion = $version;

        $this->logIdentifier = sprintf('APIMethod [%s v%s]', $this->getMethodName(), $version);

        return $this;
    }

    final protected function addParam(string $name, string|StringableInterface $label) : ParamTypeSelector
    {
        return $this->manageParams()->addParam($name, $label);
    }

    private function initReservedParams() : void
    {
        $this->manageParams()
            ->registerParam(new APIMethodParameter())
            ->registerParam(new APIVersionParameter($this));
    }

    /**
     * Retrieves the value of a request parameter. Alias for the
     * request's getParam Method.
     *
     * @param string $name
     * @param string|array<mixed>|int|float|bool|NULL $default
     * @return mixed|NULL
     */
    final protected function getParam(string $name, $default = null)
    {
        return $this->request->getParam($name, $default);
    }

    /**
     * Validates the parameters of the method to make sure all required
     * request parameters are present and valid.
     */
    final protected function validate(): void
    {
        $results = $this->manageParams()->getValidationResults();

        if($results->isValid()) {
            return;
        }

        $response = $this->errorResponseBadRequest();

        $this->configureValidationErrorResponse($response, $results);

        $response->send();
    }

    public function getValidationResults() : ParamValidationResults
    {
        return $this->manageParams()->getValidationResults();
    }

    abstract protected function configureValidationErrorResponse(ErrorResponse $response, ParamValidationResults $results) : void;

    protected function isSimulation(): bool
    {
        return Application::isSimulation();
    }

    /**
     * Utility method: Fetches the raw request body.
     * @return string
     */
    final protected function getRequestBody() : string
    {
        $raw = $this->requestBody ?? file_get_contents('php://input');

        if($raw !== false)
        {
            return trim($raw);
        }

        $this->errorResponse(JSONRequestInterface::ERROR_FAILED_TO_READ_INPUT)
            ->setErrorMessage('Failed to read request input data.')
            ->send();
    }

    private ?string $requestBody = null;

    public function setRequestBody(string $body) : self
    {
        $this->requestBody = $body;
        return $this;
    }

    final public function allowCORSDomain(string $domain) : self
    {
        $this->CORS->allowDomain($domain);
        return $this;
    }

    final public function getCORS() : Application_CORS
    {
        if(!isset($this->CORS)) {
            $this->CORS = new Application_CORS();
        }

        return $this->CORS;
    }

    public function getActiveVersion() : string
    {
        if(isset($this->selectedVersion)) {
            return $this->selectedVersion;
        }

        $requestedVersion = (string)$this->request->getParam(APIMethodInterface::REQUEST_PARAM_API_VERSION);

        if(!empty($requestedVersion) && in_array($requestedVersion, $this->getVersions())) {
            $this->selectVersion($requestedVersion);
            return $requestedVersion;
        }

        $version =$this->getCurrentVersion();

        $this->selectVersion($version);

        return $version;
    }

    /**
     * Fetch all required data from the request before
     * building the response, to ensure everything is
     * present.
     *
     * Store all needed data internally in the method for
     * use in the {@see self::collectResponseData()} method.
     *
     * In case of an error, use {@see self::errorResponse()}.
     *
     * @param string $version The API version for which to collect data.
     * @return void
     */
    abstract protected function collectRequestData(string $version) : void;

    /**
     * Add the method's custom response data to the response object,
     * if relevant for the response format of this method (an HTML-only
     * method does not need to do anything here, for example).
     *
     * @param ArrayDataCollection $response
     * @param string $version The API version for which to generate the response.
     * @return void
     */
    abstract protected function collectResponseData(ArrayDataCollection $response, string $version) : void;

    /**
     * Implementation for sending an error response for the specific
     * data format.
     *
     * > NOTE: The HTTP status code has already been sent in the header
     * > at this point. This method is only responsible for sending
     * > the body of the response, if any.
     *
     * @param ErrorResponse $response
     * @return void
     */
    abstract protected function _sendErrorResponse(ErrorResponse $response) : void;

    private function sendErrorResponse(ErrorResponse $response) : never
    {
        // In return mode, throw an exception that will be caught
        // when calling the method's processReturn() method.
        if($this->return)
        {
            throw new APIResponseDataException($this, $response->toPayload());
        }

        header('HTTP/1.1 ' . $response->getHttpStatusCode() . ' ' . str_replace(array("\n", "\r"), ' ', strip_tags($response->getErrorMessage())));

        // initialize cross-domain requests
        $this->getCORS()->init();

        $this->_sendErrorResponse($response);

        $this->processExit();
    }

    protected function errorResponse(int $errorCode) : ErrorResponse
    {
        return new ErrorResponse($this, $errorCode, $this->sendErrorResponse(...))
            ->addData($this->collectRequestErrorData());
    }

    protected function errorResponseBadRequest() : ErrorResponse
    {
        return new ErrorResponse($this, APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $this->sendErrorResponse(...))
            ->makeBadRequest()
            ->setErrorMessage('Missing or invalid parameters in request.')
            ->addData($this->collectRequestErrorData());
    }

    /**
     * Can be used to collect additional data to be added
     * to the error response. By default, returns an empty
     * array.
     *
     * @return array<int|string,mixed>
     */
    protected function collectRequestErrorData() : array
    {
        return array();
    }

    /**
     * @param ArrayDataCollection $data
     * @return never
     * @throws APIResponseDataException When in return mode - see class description.
     */
    private function sendSuccessResponse(ArrayDataCollection $data) : never
    {
        // In return mode, throw an exception that will be caught
        // when calling the method's processReturn() method.
        if($this->return)
        {
            throw new APIResponseDataException($this, $this->prepareResponse($data->getData()));
        }

        if ($this->isSimulation())
        {
            $this->logHeader('API METHOD RESPONSE');
            $this->log('Data to send:');
            $this->logData($data);
            $this->processExit();
        }

        header('HTTP/1.1 200 OK');

        // initialize cross-domain requests
        $this->getCORS()->init();

        $this->_sendSuccessResponse($data);

        $this->processExit();
    }

    /**
     * @param ArrayDataCollection $data
     * @return void
     */
    abstract protected function _sendSuccessResponse(ArrayDataCollection $data) : void;

    private ?JSONInfoSerializer $info = null;

    final public function getInfo() : JSONInfoSerializer
    {
        if(!isset($this->info)) {
            $this->info = new JSONInfoSerializer($this);
        }

        return $this->info;
    }

    final protected function processExit() : never
    {
        Application::exit('API Method [' . $this->getID() . '] has finished.');
    }

    final public function getRelatedMethods(): array
    {
        $result = array();
        $manager = APIManager::getInstance();

        foreach($this->getRelatedMethodNames() as $methodName) {
            $result[] = $manager->getMethodByName($methodName);
        }

        usort($result, static function(APIMethodInterface $a, APIMethodInterface $b) : int {
            return strnatcasecmp($a->getMethodName(), $b->getMethodName());
        });

        return $result;
    }
}
