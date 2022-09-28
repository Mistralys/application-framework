<?php
/**
 * @package Connectors
 * @subpackage Response
 * @see Connectors_Response
 */

declare(strict_types=1);

use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\ConvertHelper_ThrowableInfo;
use Connectors\Response\ResponseError;
use Connectors\Response\ResponseSerializer;

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

    public const ERROR_INVALID_SERIALIZED_DATA = 80001;
    public const ERROR_DATA_SET_IS_MISSING = 80002;

    public const STATE_ERROR = 'error';
    public const STATE_SUCCESS = 'success';

    public const JSON_PLACEHOLDER_START = '{RESPONSE}';
    public const JSON_PLACEHOLDER_END = '{/RESPONSE}';

    public const KEY_STATE = 'state';
    public const KEY_DETAILS = 'details';
    public const KEY_MESSAGE = 'message';
    public const KEY_DATA = 'data';
    public const KEY_CODE = 'code';
    public const KEY_EXCEPTION = 'exception';

    public const RETURNCODE_JSON_NOT_PARSEABLE = 400001;
    public const RETURNCODE_UNEXPECTED_FORMAT = 400002;
    public const RETURNCODE_ERROR_RESPONSE = 400003;
    public const RETURNCODE_WRONG_STATUSCODE = 400004;

    protected Connectors_Request $request;
    protected HTTP_Request2_Response $result;
    protected Connectors_Connector $connector;
    private ?ConvertHelper_ThrowableInfo $exception = null;
    protected string $json = '';

    /**
     * @var ArrayDataCollection|NULL
     */
    private ?ArrayDataCollection $responseData = null;

    /**
     * @var ResponseError|null
     */
    protected ?ResponseError $error = null;

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
        
        if(!$request instanceof Connectors_Request_Method || $this->isError()) {
            return;
        }
        
        // method requests expect a specific return format
        if (!$this->responseData->keyExists(self::KEY_STATE)) {
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

        if($this->responseData->keyHasValue(self::KEY_EXCEPTION))
        {
            $this->exception = ConvertHelper_ThrowableInfo::fromSerialized(
                $this->responseData->getJSONArray(self::KEY_EXCEPTION)
            );
        }

        if($this->responseData->getString(self::KEY_STATE) === self::STATE_ERROR)
        {
            $this->setError(
                t('The server returned an error.'),
                'Use the getEndpointXXX() methods to get the error details.',
                self::RETURNCODE_ERROR_RESPONSE
            );
        }
    }

    /**
     * If the endpoint sent exception details in the response payload,
     * it can be accessed here.
     *
     * NOTE: This is available even if the response is considered valid,
     * and exception details are present.
     *
     * @return ConvertHelper_ThrowableInfo|null
     */
    public function getEndpointException() : ?ConvertHelper_ThrowableInfo
    {
        return $this->exception;
    }

    public function getResponseState() : string
    {
        return $this->responseData->getString(self::KEY_STATE);
    }

    /**
     * When the response returned an error, this is available
     * to get the error message that the endpoint sent.
     *
     * ## Usage
     *
     * 1. Check if the response has errors {@see Connectors_Response::isError()},
     * 2. Check that the code {@see Connectors_Response::getErrorCode()} matches {@see Connectors_Response::RETURNCODE_ERROR_RESPONSE},
     * 3. Get the endpoint error.
     *
     * If an exception was included in the endpoint, it is
     * available via {@see ResponseError::getException()}.
     *
     * @return ResponseError|null
     */
    public function getEndpointError() : ?ResponseError
    {
        if($this->getResponseState() !== self::STATE_ERROR) {
            return null;
        }

        return new ResponseError(
            $this->responseData->getString(self::KEY_MESSAGE),
            $this->responseData->getString(self::KEY_DETAILS),
            $this->responseData->getInt(self::KEY_CODE),
            $this->getEndpointException()
        );
    }

    private function extractDataFromBody(string $body) : ArrayDataCollection
    {
        $body = trim($body);

        if(empty($body)) {
            return ArrayDataCollection::create();
        }

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
        }
        else
        {
            $this->json = $body;
        }

        try
        {
            $decoded = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e)
        {
            $decoded = null;
        }

        if(is_array($decoded))
        {
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

    public function getErrorMessage() : string
    {
        if (isset($this->error)) {
            return $this->error->getMessage();
        }

        return '';
    }
    
    public function getErrorCode() : int
    {
        if(isset($this->error)) {
            return $this->error->getCode();
        }
        
        return 0;
    }
    
    public function getErrorDetails() : string
    {
        if(isset($this->error)) {
            return $this->error->getDetails();
        }
        
        return '';
    }
    
    public function getErrorData() : array
    {
        return $this->getData();
    }

   /**
    * Retrieves the full URL that has been requested. If any parameters
    * were sent via post, they are included as well.
    * 
    * @return string
    */
    public function getURL() : string
    {
        $url = $this->result->getEffectiveUrl();
        
        $data = $this->request->getPostData();
        if(!empty($data)) 
        {
            $connect = '?';
            if(strpos($url, '?') !== false) {
                $connect = '&';
            }
            
            $url .= $connect.http_build_query($data);
        }
        
        return $url;
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
     * @return array<string,mixed>
     */
    public function getData() : array
    {
        return $this->responseData->getArray(self::KEY_DATA);
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
        $details = $this->getErrorDetails();
        
        $eMessage = $this->getErrorMessage();
        if(!empty($message)) {
            $eMessage = $message.' | Connector message: '.$eMessage;
        }
        
        $eCode = $this->getErrorCode();
        if(!empty($code)) {
            $eMessage = 'Connector code: '.$eCode.' | '.$eMessage;
            $eCode = $code;
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
    
    public static function unserialize(string $serialized) : Connectors_Response
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
        $codes = array((string)$this->getErrorCode());

        $error = $this->getEndpointError();

        if($error !== null) {
            $codes[] = (string)$error->getCode();
        }

        $exception = $this->getEndpointException();
        if($exception !== null) {
            $this->getExceptionCodesRecursive($exception, $codes);
        }

        $codes = array_unique($codes);
        $result = array();

        foreach($codes as $code) {
            if($code !== '' && $code !== '0') {
                $result[] = $code;
            }
        }

        return $result;
    }

    /**
     * @param ConvertHelper_ThrowableInfo $info
     * @param string[] $result
     * @return string[]
     * @throws ConvertHelper_Exception
     */
    protected function getExceptionCodesRecursive(ConvertHelper_ThrowableInfo $info, array &$result=null) : array
    {
        $result[] = (string)$info->getCode();

        if($info->hasPrevious()) {
            $this->getExceptionCodesRecursive($info->getPrevious(), $result);
        }

        return $result;
    }
}
