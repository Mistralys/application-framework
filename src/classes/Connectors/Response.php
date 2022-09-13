<?php
/**
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_ThrowableInfo;
use Connectors\Response\ResponseError;

/**
 * Information on a single response of a connector request.
 * Allows accessing information on the state of the request,
 * including detailed failure information, if any.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Response implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_INVALID_SERIALIZED_DATA = 80001;

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
     * @var array<string,mixed>
     */
    private array $responseData;

    /**
     * @var array<mixed>
     */
    protected array $data = array();

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
        
        if(!$request instanceof Connectors_Request_Method) {
            return;
        }
        
        // method requests expect a specific return format
        if (!isset($this->responseData[self::KEY_STATE])) {
            $this->setError(
                t('The server response could not be read.'),
                sprintf(
                    'The parsed json does not contain the [%s] key. Given keys: [%s].',
                    self::KEY_STATE,
                    implode(', ', array_keys($this->responseData))
                ),
                self::RETURNCODE_UNEXPECTED_FORMAT    
            );
            return;
        }

        if(isset($this->responseData[self::KEY_EXCEPTION]) && !empty($this->responseData[self::KEY_EXCEPTION]))
        {
            $this->exception = ConvertHelper_ThrowableInfo::fromSerialized($this->responseData[self::KEY_EXCEPTION]);
        }

        if($this->responseData[self::KEY_STATE] === self::STATE_ERROR)
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
        return $this->responseData[self::KEY_STATE] ?? '';
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
            $this->responseData[self::KEY_MESSAGE],
            $this->responseData[self::KEY_DETAILS] ?? '',
            $this->responseData[self::KEY_CODE] ?? 0,
            $this->getEndpointException()
        );
    }

    private function extractDataFromBody(string $body) : ?array
    {
        $body = trim($body);

        if(empty($body)) {
            return array();
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
            return $decoded;
        }

        $this->setError(
            t('The server did not answer with valid JSON as expected.'),
            sprintf(
                'The request return string could not be parsed as json, or no %s tags were present.',
                self::JSON_PLACEHOLDER_START
            ),
            self::RETURNCODE_JSON_NOT_PARSEABLE
        );

        return null;
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

    public function getData() : array
    {
        if(isset($this->responseData[self::KEY_DATA]) && is_array($this->responseData[self::KEY_DATA])) {
            return $this->responseData[self::KEY_DATA];
        }

        return array();
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
        return ConvertHelper::var2json(array(
            'statusCode' => $this->getStatusCode(),
            'url' => $this->request->getRequestURL(),
            'statusMessage' => $this->getStatusMessage(),
            'body' => $this->getRawJSON()
        ));
    }
    
    public static function unserialize(Connectors_Request $request, string $serialized) : Connectors_Response
    {
        try
        {
            $data = json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e)
        {
            $ex = new Connectors_Exception(
                $request->getConnector(),
                'Invalid serialized data.',
                '',
                self::ERROR_INVALID_SERIALIZED_DATA,
                $e
            );

            $ex->setRequest($request);

            throw $ex;
        }

        $logPrefix = $request->getLogIdentifier().' | Response | ';

        Application::log(sprintf(
            '%sCreating from serialized data. Status: [%s %s]. URL: [%s].',
            $logPrefix,
            $data['statusCode'],
            $data['statusMessage'],
            $data['url']
        ));

        $response = new HTTP_Request2_Response(
            sprintf(
                'HTTP/1.0 %s %s',
                $data['statusCode'],
                $data['statusMessage']
            ),
            false,
            $data['url']
        );

        $response->appendBody($data['body']);

        return new Connectors_Response($request, $response);
    }

    public function getLogIdentifier(): string
    {
        return $this->request->getLogIdentifier().' | Response';
    }
}
