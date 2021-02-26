<?php

class Connectors_Exception extends Application_Exception
{
   /**
    * @var Connectors_Connector
    */
    protected $connector;
    
   /**
    * @var HTTP_Request2_Response|NULL
    */
    protected $response = null;
    
   /**
    * @var Connectors_Request|NULL
    */
    protected $request = null;
    
    public function __construct(Connectors_Connector $connector, string $message, string $developerInfo = '', int $code = 0, $previous = null)
    {
        parent::__construct($message, $developerInfo, $code, $previous);
        
        $this->connector = $connector;
    }
    
    public function getConnector() : Connectors_Connector
    {
        return $this->connector;
    }
    
    public function setRequest(Connectors_Request $request) : Connectors_Exception
    {
        $this->request = $request;
        
        return $this;
    }
    
    public function hasRequest() : bool
    {
        return isset($this->request);
    }
    
    public function getRequest() : ?Connectors_Request
    {
        return $this->request;
    }
    
    public function setResponse(HTTP_Request2_Response $response) : Connectors_Exception
    {
        $this->response = $response;
        
        return $this;
    }
    
    public function hasResponse() : bool
    {
        return isset($this->response);
    }
    
    public function getResponse() : ?HTTP_Request2_Response
    {
        return $this->response;
    }
    
    public function getDeveloperInfo() : string
    {
        $lines = array();
        $details = (string)parent::getDeveloperInfo();
        
        if(!empty($details))
        {
            $lines[] = $details;
        }
        
        if(isset($this->response))
        {
            $lines[] = sprintf('Requested URL: [%1$s]', $this->response->getEffectiveUrl());
            $lines[] = sprintf('Response status code: [%1$s]', $this->response->getStatus());
            $lines[] = sprintf('Response status message: [%1$s].', $this->response->getReasonPhrase());
            $lines[] = '';
            $lines[] = 'Response headers:';
            
            $headers = $this->response->getHeader();
            foreach($headers as $name => $value)
            {
                $lines[] = $name.' = '.$value;
            }
            
            $lines[] = '';
            $lines[] = 'Response body:';
            $lines[] = $this->parseBody($this->response->getBody());
            $lines[] = '';
        }
        
        if(isset($this->request))
        {
            $this->request->getHeaders();
            $lines[] = sprintf('Request method: [%s]', $this->request->getHTTPMethod());
            $lines[] = '';
            $lines[] = 'Request headers:';
            
            $headers = $this->request->getHeaders();
            foreach($headers as $name => $value)
            {
                $lines[] = $name.' = '.$value;
            }
            
            $body = $this->request->getBody();
            
            $lines[] = 'Request body:';
            $lines[] = $this->parseBody($body);
            $lines[] = '';
            
            $lines[] = 'Request variables:';
            $data = $this->request->getPostData();
            foreach($data as $key => $val)
            {
                $lines[] = $key.' = '.$val;
            }
            
            $lines[] = '';
        }
        
        if(isCLI())
        {
            return implode(PHP_EOL, $lines);
        }
        
        return implode('<br>', $lines);
    }
    
    protected function parseBody(string $source) : string
    {
        $source = trim($source);
        
        if(strstr($source, '{'))
        {
            $data = @json_decode($source, true);
            
            if(is_array($data))
            {
                $source = json_encode($data, JSON_PRETTY_PRINT);
            }
        }
        
        if(empty($source))
        {
            $source = '(empty string)';
        }
        
        return $this->pre($source);
    }
    
    protected function pre(string $text) : string
    {
        if(isCLI()) 
        {
            return $text;
        }
        
        return '<pre>'.$text.'</pre>';
    }
}
