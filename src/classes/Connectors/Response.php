<?php

use AppUtils\ConvertHelper;

class Connectors_Response implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_INVALID_SERIALIZED_DATA = 80001;

    /**
     * @var array|null
     */
    protected $data = null;

   /**
    * @var Connectors_Request
    */
    protected $request;
    
   /**
    * @var HTTP_Request2_Response
    */
    protected $result;
    
   /**
    * @var Connectors_Connector
    */
    protected $connector;
    
    const RETURNCODE_JSON_NOT_PARSEABLE = 400001;
    const RETURNCODE_UNEXPECTED_FORMAT = 400002;
    const RETURNCODE_ERROR_RESPONSE = 400003;
    const RETURNCODE_WRONG_STATUSCODE = 400004;
    
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
        
        $body = $result->getBody();
        $json = '';
        $data = array();
        
        if(!empty($body))
        {
            // if logging is enabled, the client may wrap the 
            // actual response in the tags: {RESPONSE}response_data{/RESPONSE}
            // The body contained in these tags is then used instead.
            if(strstr($body, '{RESPONSE}')) 
            {
                $regs = array();
                if(preg_match('%{RESPONSE}(.*){/RESPONSE}%six', $body, $regs)) 
                {
                    $json = $regs[1];
                }
            }
            else
            {
                $json = $body;
            }
            
            $data = json_decode($json, true);
            
            if(empty($data)) 
            {
                $this->setError(
                    t('The server did not answer with valid JSON as expected.'),
                    'The request return string could not be parsed as json, or no {RESPONSE} tags were present.',
                    self::RETURNCODE_JSON_NOT_PARSEABLE,
                    $body   
                );
                return;
            }
        }
        
        if(!$request instanceof Connectors_Request_Method) {
            $this->data = $data;
            return;
        }
        
        // method requests expect a specific return format
        if (!is_array($data) || !isset($data['state'])) {
            $this->setError(
                t('The server response could not be read.'),
                sprintf(
                    'The parsed json was either not an array [%s] or the [state] key was not present.',
                    gettype($data)
                ),
                self::RETURNCODE_UNEXPECTED_FORMAT    
            );
            return;
        }
        
        if($data['state'] == 'error') {
            if(!isset($data['details'])) {
                $data['details'] = '';
            }
            
            $this->setError(
                t('The server returned an error:') . $data['message'],
                $data['details'],
                self::RETURNCODE_ERROR_RESPONSE,
                $data 
            );
            return;
        }

        $this->data = $data['data'];
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
    public function getRawJSON()
    {
        return $this->result->getBody();
    }
    
    public function getTimeTaken()
    {
        return $this->request->getTimeTaken();
    }

    public function isError()
    {
        return isset($this->error);
    }
    
    public function getHeader(string $name) : string
    {
        return (string)$this->result->getHeader($name);
    }
    
    protected $error;
    
    protected function setError($message, $details, $code, $data=null)
    {
        $this->log(sprintf(
            'Error [%s] | %s | %s',
            $code,
            $message,
            $details
        ));
        
        $this->error = array(
            'message' => $message,
            'code' => $code,
            'details' => $details,
            'data' => $data
        );
    }

    public function getErrorMessage()
    {
        if (isset($this->error)) {
            return $this->error['message'];
        }

        return null;
    }
    
    public function getErrorCode()
    {
        if(isset($this->error)) {
            return $this->error['code'];
        }
        
        return null;
    }
    
    public function getErrorDetails()
    {
        if(isset($this->error)) {
            return $this->error['details'];
        }
        
        return null;
    }
    
    public function getErrorData()
    {
        if(isset($this->error)) {
            return $this->error['data'];
        }
        
        return null;
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
            if(strstr($url, '?')) {
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
    
    public function getMethod()
    {
        return $this->request->getHTTPMethod();
    }

    public function getData() : array
    {
        if(isset($this->data)) {
            return $this->data;
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
            'body' => $this->getBody()
        ));
    }
    
    public static function unserialize(Connectors_Request $request, string $serialized) : Connectors_Response
    {
        $data = json_decode($serialized, true);
        if($data === false)
        {
            $ex = new Connectors_Exception(
                $request->getConnector(),
                'Invalid serialized data.',
                '',
                self::ERROR_INVALID_SERIALIZED_DATA
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
