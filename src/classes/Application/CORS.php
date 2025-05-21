<?php
/**
 * @package Application
 * @subpackage Core
 */

declare(strict_types=1);

/**
 * Helper class used to enable CORS requests: whenever cross-domain
 * requests should be allowed, this can be used to automate the
 * client/server handshakes.
 * 
 * ### Allow all requests
 * 
 * ```php
 * $cors = new Application_CORS();
 * $cors->allowDomain('*');
 * $cors->init();
 * ```
 *
 * ### Allow specific or wildcard domains
 * 
 * ```php
 * $cors = new Application_CORS();
 * $cors->allowDomain('*'); // all hosts, any port
 * $cors->allowDomain('http://www.cats.com'); // this exact host, and only http
 * $cors->allowDomain('*.cats.com'); // http or https
 * $cors->allowDomain('http://*.dogs.net'); // any subdomain over http
 * $cors->allowDomain('*.cats.com:8080'); // only over port 8080
 * $cors->allowDomain('*:8080'); // all hosts on port 8080
 * $cors->init();
 * ```
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
 * @see http://stackoverflow.com/questions/8719276/cors-with-php-headers
 * @see http://stackoverflow.com/questions/14003332/access-control-allow-origin-wildcard-subdomains-ports-and-protocols#30394641
 */
class Application_CORS
{
    /**
     * @var array<int,array{host:string,port:string}>
     */
    protected array $domains = array();
    
    /**
     * Adds a domain name to the list of allowed cross-origin
     * request sources. Adding one of these enables CORS for
     * this API endpoint.
     *
     * > Note: use the wildcard `*` as domain to enable
     * > all cross-origin sources. This supersedes any other specific
     * > domains that may have been added.
     *
     * ## Examples
     * 
     * ```php
     * $cors->allowDomain('*'); // all hosts, any port
     * $cors->allowDomain('http://www.cats.com'); // this exact host, and only http
     * $cors->allowDomain('*.cats.com'); // http or https
     * $cors->allowDomain('http://*.dogs.net'); // any subdomain over http
     * $cors->allowDomain('*.cats.com:8080'); // only over port 8080
     * $cors->allowDomain('*:8080'); // all hosts on port 8080
     * ```
     *
     * @param string $domain The domain to allow. Should include the scheme, and optionally the port if needed.
     * @return $this
     */
    public function allowDomain(string $domain) : self
    {
        $host = $domain;
        $port = '*';
        
        if(strpos($domain, ':') !== false) {
            $tokens = explode(':', $domain);
            $host = $tokens[0];
            $port = $tokens[1];
        }
        
        $this->domains[] = array(
            'host' => $host,
            'port' => $port
        );
        
        return $this;
    }
    
    /**
     * Called when CORS requests have been enabled by adding one or
     * more allowed domains using the {@link allowCORSDomain()} method.
     * Handles the CORS Preflight handshake if present, and sends the
     * "allow" header if the origin of the request is valid.
     */
    public function init() : void
    {
        if(isCLI()) {
            return;
        }

        // CORS-Enabled requests should send this header, even when the
        // actual request is disallowed.
        header('Vary: Origin');
    
        if(empty($this->domains)) {
            return;
        }
        
        // CORS uses the OPTIONS method to handle handshakes prior to
        // the actual data request, to see if the request is allowed.
        if(strtolower($_SERVER['REQUEST_METHOD']) === 'options')
        {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
            }
    
            if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header(sprintf(
                    'Access-Control-Allow-Headers: %s',
                    implode(', ', array(
                        'X-Requested-With',
                        'Content-Type',
                        'Access-Control-Allow-Origin',
                        'Access-Control-Allow-Methods',
                        'Access-Control-Allow-Headers',
                        'Depth',
                        'User-Agent',
                        'X-File-Size',
                        'X-Requested-With',
                        'If-Modified-Since',
                        'X-File-Name',
                        'Cache-Control'
                    ))
                ));
            }
    
            exit;
        }
    
        $origin = $this->getOrigin();
        
        // A CORS origin is present, add the CORS header
        if($origin !== null)
        {
            // origin does not match? This request is not allowed, stop sending
            // any headers. As long as the "allow" header is not sent, the client
            // will assume that the request is denied.
            $match = $this->getMatchedCORSDomain($origin);
            if($match === null) {
                // Usually, no header is needed to be sent at this point:
                // the browser is supposed to signal the request as blocked.
                // However, Chrome has some versions in which this fails, and
                // the request is simply shown as being empty.
                //
                // To make it at least easier to debug, this forbidden header was added.
                header('HTTP/1.1 403 Access forbidden by CORS rules for '.$origin);
                exit;
            }
            
            header('Access-Control-Allow-Origin: '.$match);
            header('Access-Control-Allow-Credentials: true');
        }
    }
    
   /**
    * Retrieves the value of the HTTP_ORIGIN header, if any.
    * @return string|NULL
    */
    protected function getOrigin() : ?string
    {
        if(isset($this->simulateOriginDomain)) {
            return $this->simulateOriginDomain;
        }
        
        return $_SERVER['HTTP_ORIGIN'] ?? null;
    }
    
   /**
    * Retrieves the string to use for the <code>Access-Control-Allow-Origin</code>
    * header. This can be the wildcard, <code>*</code>, the exact-matched domain,
    * e.g. <code>http://www.matched-domain.com</code> or <code>none</code> if the
    * origin is not trusted. 
    * 
    * @param string $origin
    * @return string|NULL
    */
    protected function getMatchedCORSDomain(string $origin) : ?string
    {
        $info = parse_url($origin);

        $originPort = null; 
        if(isset($info['port'])) { 
            $originPort = $info['port']; 
        }
        
        $originHost = $info['scheme'].'://'.$info['host'];

        // go through the wildcard domains first
        $keep = array();
        foreach($this->domains as $domain) {
            if($domain['host'] === '*') {
                if($this->isPortAllowed($originPort, $domain['port'])) {
                    return '*';
                }
                continue;
            } 
            
            $keep[] = $domain;
        }
        
        foreach($keep as $domain)
        {
            // escape all special characters except the wildcard, 
            // which is replaced with capturing groups.
            $host = str_replace('*', '_WC_', $domain['host']);
            $host = preg_quote($host, '/');
            $host = str_replace('_WC_', '(.*)', $host);
    
            $regexp = '/^' . $host . '$/';
    
            if (preg_match($regexp, $originHost) && $this->isPortAllowed($originPort, $domain['port'])) {
                return $origin;
            }
        }
    
        return null;
    }
    
   /**
    * Checks whether the specified port is allowed.
    * @param string|integer $port
    * @param string|integer $allowedPort
    * @return boolean
    */
    protected function isPortAllowed($port, $allowedPort) : bool
    {
        return $allowedPort === '*' || (int)$port === (int)$allowedPort;
    }

    protected ?string $simulateOriginDomain = null;
    
   /**
    * Allows simulating a specific string sent as origin in the
    * request headers. Does not modify the headers themselves.
    * 
    * NOTE: Must be set before calling {@link init()}.
    * 
    * @param string $domain
    * @return $this
    */
    public function simulateOrigin(string $domain) : self
    {
        $this->simulateOriginDomain = $domain;
        return $this;
    }
}
