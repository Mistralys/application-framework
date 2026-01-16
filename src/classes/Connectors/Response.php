<?php
/**
 * @package Connectors
 * @subpackage Response
 * @see Connectors_Response
 */

declare(strict_types=1);

use AppUtils\ArrayDataCollection;
use AppUtils\ThrowableInfo;
use Connectors\Response\ResponseEndpointError;
use Connectors\Response\ResponseError;
use Connectors\Response\ResponseSerializer;
use function AppUtils\parseURL;

/**
 * Information on a single response of a connector request.
 * Allows accessing information on the state of the request,
 * including detailed failure information, if any.
 *
 * @package Connectors
 * @subpackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Response implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const int ERROR_INVALID_SERIALIZED_DATA = 80001;
    public const int ERROR_DATA_SET_IS_MISSING = 80002;

    public const string STATE_ERROR = 'error';
    public const string STATE_SUCCESS = 'success';

    public const string JSON_PLACEHOLDER_START = '{RESPONSE}';
    public const string JSON_PLACEHOLDER_END = '{/RESPONSE}';

    public const string KEY_STATE = 'state';
    public const string KEY_DETAILS = 'details';
    public const string KEY_MESSAGE = 'message';
    public const string KEY_DATA = 'data';
    public const string KEY_CODE = 'code';
    public const string KEY_EXCEPTION = 'exception';

    public const int RETURNCODE_JSON_NOT_PARSEABLE = 400001;
    public const int RETURNCODE_UNEXPECTED_FORMAT = 400002;
    public const int RETURNCODE_ERROR_RESPONSE = 400003;
    public const int RETURNCODE_WRONG_STATUSCODE = 400004;

    protected Connectors_Request $request;
    protected HTTP_Request2_Response $result;
    protected Connectors_Connector $connector;
    private ?ThrowableInfo $exception = null;
    protected string $json = '';

    /**
     * @var ArrayDataCollection|NULL
     */
    private ?ArrayDataCollection $responseData = null;

    /**
     * @var ResponseError|null
     */
    protected ?ResponseError $error = null;
    private bool $looseData = false;

    public function __construct(Connectors_Request $request, HTTP_Request2_Response $result)
    {
        $this->request = $request;
        $this->result = $result;
        $this->connector = $request->getConnector();
        
        $this->log(sprintf('Requested url is [%s].', $result->getEffectiveUrl()));
        $this->log(sprintf('Response status is [%s].', $result->getStatus()));
        
        if(!$request->isValidResponseCode($result->getStatus())) 
        {
            $this->setError(
                t('The target page did not report the expected status code.'), 
                sprintf('Got code [%s].', $result->getStatus()), 
                self::RETURNCODE_WRONG_STATUSCODE
            );
            return;
        }
        
        $this->responseData = $this->extractDataFromBody($result->getBody());

        // Only method requests have the "state" key, so any data extracted
        // from the response must be considered loose data.
        if(!$request instanceof Connectors_Request_Method || $this->isError())
        {
            $this->log('Loose data detected.');
            $this->looseData = true;
            return;
        }

        // method requests expect a specific return format
        if (!$this->responseData->keyExists(self::KEY_STATE))
        {
            $this->setError(
                t('The server response could not be read.'),
                sprintf(
                    'The parsed json does not contain the [%s] key. Given keys: [%s].',
                    self::KEY_STATE,
                    implode(', ', array_keys($this->responseData->getData()))
                ),
                self::RETURNCODE_UNEXPECTED_FORMAT    
            );
            return;
        }

        $state = $this->getResponseState();

        $this->log('Response state is [%s].', $state);

        if($state !== self::STATE_ERROR) {
            return;
        }

        $this->log('Error state | Fetching error details.');

        if($this->responseData->keyHasValue(self::KEY_EXCEPTION))
        {
            $this->log('Error state | Found exception data.');

            $this->exception = ThrowableInfo::fromSerialized(
                $this->responseData->getJSONArray(self::KEY_EXCEPTION)
            );
        }

        $this->error = new ResponseEndpointError(
            $this->responseData->getString(self::KEY_MESSAGE),
            $this->responseData->getString(self::KEY_DETAILS),
            $this->responseData->getInt(self::KEY_CODE),
            $this->responseData->getArray(self::KEY_DATA),
            $this->exception
        );
    }

    /**
     * If the endpoint sent exception details in the response payload,
     * it can be accessed here.
     *
     * NOTE: This is available even if the response is considered valid,
     * and exception details are present.
     *
     * @return ThrowableInfo|null
     * @deprecated Use {@see Connectors_Response::getError()} and {@see ResponseEndpointError::getEndpointError()} instead.
     */
    public function getEndpointException() : ?ThrowableInfo
    {
        $error = $this->getError();

        if($error instanceof ResponseEndpointError) {
            return $error->getEndpointError()->getException();
        }

        return $this->exception;
    }

    public function getResponseState() : string
    {
        return $this->responseData->getString(self::KEY_STATE);
    }

    /**
     * @return ResponseError|null
     * @deprecated Use {@see Connectors_Response::getError()} and {@see ResponseEndpointError::getEndpointError()} instead.
     */
    public function getEndpointError() : ?ResponseError
    {
        if($this->error instanceof ResponseEndpointError) {
            return $this->error->getEndpointError();
        }

        return null;
    }

    private function extractDataFromBody(string $body) : ArrayDataCollection
    {
        $body = trim($body);

        if(empty($body))
        {
            $this->log('ExtractData | Empty body, ignoring.');

            return ArrayDataCollection::create();
        }

        $this->log('ExtractData | Body length: [%s] characters.', strlen($body));

        // if logging is enabled, the client may wrap the
        // actual response in the tags: {RESPONSE}response_data{/RESPONSE}
        // The body contained in these tags is then used instead.
        if(strpos($body, self::JSON_PLACEHOLDER_START) !== false)
        {
            $regex = sprintf(
                '!%s(.*)%s!six',
                preg_quote(self::JSON_PLACEHOLDER_START, '!'),
                preg_quote(self::JSON_PLACEHOLDER_END, '!')
            );

            $regs = array();
            if(preg_match($regex, $body, $regs))
            {
                $this->json = $regs[1];
            }

            $this->log('ExtractData | Extraneous content detected. JSON extracted using placeholders.');
        }
        else
        {
            $this->json = $body;
        }

        $this->log('ExtractData | JSON content length: [%s].', strlen($this->json));

        try
        {
            $decoded = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e)
        {
            $this->log('ExtractData | The JSON could not be decoded: [%s]', $e->getMessage());
            $this->logData(array('json' => $this->json));

            $decoded = null;
        }

        if(is_array($decoded))
        {
            $this->log('ExtractData | JSON successfully decoded.');

            return ArrayDataCollection::create($decoded);
        }

        $this->setError(
            t('The server did not answer with valid JSON as expected.'),
            sprintf(
                'The request return string could not be parsed as json, or no %s tags were present.',
                self::JSON_PLACEHOLDER_START
            ),
            self::RETURNCODE_JSON_NOT_PARSEABLE
        );

        return ArrayDataCollection::create();
    }
    
    public function getRequest() : Connectors_Request
    {
        return $this->request;
    }

    public function getStatusCode() : int
    {
        return $this->result->getStatus();
    }

    public function getStatusMessage() : string
    {
        return $this->result->getReasonPhrase();
    }
    
   /**
    * Retrieves the raw JSON from the remote API request.
    * @return string
    */
    public function getRawJSON() : string
    {
        return $this->json;
    }
    
    public function getTimeTaken() : float
    {
        return $this->request->getTimeTaken();
    }

    public function isError() : bool
    {
        return isset($this->error);
    }

    public function isLooseData() : bool
    {
        return $this->looseData;
    }
    
    public function getHeader(string $name) : string
    {
        return (string)$this->result->getHeader($name);
    }

    /**
     * @param string $message
     * @param string $details
     * @param int $code
     * @return void
     */
    protected function setError(string $message, string $details, int $code) : void
    {
        $this->log(sprintf(
            'Error [%s] | %s | %s',
            $code,
            $message,
            $details
        ));
        
        $this->error = new ResponseError(
            $message,
            $details,
            $code
        );
    }

    /**
     * There are two types of errors:
     *
     * 1. The response does not match the expectations. This can be an
     *    HTTP response code not in the expected list, or the format
     *    could not be recognized for example. The error will be an
     *    instance of {@see ResponseError}.
     *
     * 2. The endpoint explicitly returned an error message. The error
     *    object will be an instance of {@see ResponseEndpointError},
     *    which offers additional error details as provided by the
     *    endpoint. This can include exception details.
     *
     * @return ResponseError|null
     */
    public function getError() : ?ResponseError
    {
        return $this->error;
    }

    /**
     * @return string
     * @deprecated Use getError()->getMessage() instead.
     */
    public function getErrorMessage() : string
    {
        if (isset($this->error)) {
            return $this->error->getMessage();
        }

        return '';
    }

    /**
     * @return int
     * @deprecated Use getError()->getCode() instead.
     */
    public function getErrorCode() : int
    {
        if(isset($this->error)) {
            return $this->error->getCode();
        }
        
        return 0;
    }

    /**
     * @return string
     * @deprecated Use getError()->getDetails() instead.
     */
    public function getErrorDetails() : string
    {
        if(isset($this->error)) {
            return $this->error->getDetails();
        }
        
        return '';
    }

    /**
     * @return array<int|string,mixed>
     * @deprecated Use getError()->getCode() instead.
     */
    public function getErrorData() : array
    {
        return $this->getData();
    }

   /**
    * Retrieves the full URL that has been requested. If any parameters
    * were sent via GET or POST, they are included as well. Recommended
    * to be used for verification purposes only.
    * 
    * @return string
    * @see Connectors_Request::getBaseURL()
    */
    public function getURL() : string
    {
        $url = parseURL($this->result->getEffectiveUrl());

        $data = $this->request->getGetData();

        foreach ($data as $name => $value)
        {
            $url->setParam($name, $value);
        }

        $data = $this->request->getPostData();

        foreach ($data as $name => $value)
        {
            $url->setParam($name, $value);
        }
        
        return $url->getNormalized();
    }

    public function getResult() : HTTP_Request2_Response
    {
        return $this->result;
    }
    
    public function getMethod() : string
    {
        return $this->request->getHTTPMethod();
    }

    /**
     * Retrieves the decoded JSON data returned
     * by the endpoint, if any.
     *
     * @return array<int|string,mixed>
     */
    public function getData() : array
    {
        // Success response: The data is stored in the "data" key of
        // the endpoint's payload.
        if($this->getResponseState() === self::STATE_SUCCESS) {
            return $this->responseData->getArray(self::KEY_DATA);
        }

        // Just loose data returned (response without state information)
        if($this->isLooseData()) {
            return $this->responseData->getData();
        }

        // Erroneous response, or no response
        return array();
    }

    public function requireData() : array
    {
        $data = $this->getData();

        if(!empty($data)) {
            return $data;
        }

        throw new Connectors_Exception(
            $this->connector,
            'No data specified in the response',
            'A data set is required to be sent with the response, but it is empty.',
            self::ERROR_DATA_SET_IS_MISSING
        );
    }
    
   /**
    * Throws an exception for the response error.
    * 
    * @param string $message
    * @param int $code
    * @throws Connectors_Exception
    */
    public function throwException(string $message='', int $code=0) : void
    {
        throw $this->createException($message, $code);
    }
    
    public function createException(string $message='', int $code=0) : Connectors_Exception
    {
        $error = $this->getError();
        $details = '';
        $eMessage = '';
        $eCode = 0;

        if($error !== null)
        {
            $details = $error->getDetails();
            $eMessage = $error->getMessage();
            if (!empty($eMessage)) {
                $eMessage = $message . ' | Connector message: ' . $eMessage;
            }

            $eCode = $error->getCode();
            if (!empty($code)) {
                $eMessage = 'Connector code: ' . $eCode . ' | ' . $eMessage;
                $eCode = $code;
            }
        }

        $ex = new Connectors_Exception(
            $this->request->getConnector(),
            $eMessage,
            $details,
            $eCode
        );
        
        $ex->setRequest($this->request);
        $ex->setResponse($this->result);
        $ex->setConnectorResponse($this);
        
        return $ex;
    }
    
    public function getBody() : string
    {
        return $this->result->getBody();
    }
    
    public function serialize() : string
    {
        return ResponseSerializer::serialize($this);
    }
    
    public static function unserialize(string $serialized) : ?Connectors_Response
    {
        return ResponseSerializer::unserialize($serialized);
    }

    public function getLogIdentifier(): string
    {
        return $this->request->getLogIdentifier().' | Response';
    }

    /**
     * @param string|int $code
     * @return bool
     */
    public function hasErrorCode($code) : bool
    {
        return in_array((string)$code, $this->getErrorCodes(), true);
    }

    /**
     * Retrieves all error codes available in the response,
     * including exception codes.
     *
     * @return string[]
     */
    public function getErrorCodes() : array
    {
        $error = $this->getError();

        if($error !== null) {
            return $error->getAllCodes();
        }

        return array();
    }
}
