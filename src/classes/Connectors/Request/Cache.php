<?php

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

class Connectors_Request_Cache implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    /**
     * @var Connectors_Request
     */
    private $request;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var int
     */
    private $duration = 0;

    public function __construct(Connectors_Request $request)
    {
        $this->request = $request;
    }

    public function getLogIdentifier(): string
    {
        return $this->request->getLogIdentifier().' | Cache';
    }

    public function isEnabled() : bool
    {
        return $this->enabled && $this->duration > 0;
    }

    public function isValid() : bool
    {
        $file = $this->getCacheFile();

        if(!file_exists($file))
        {
            $this->log(sprintf('Validate | File [%s] does not exist.', basename($file)));
            return false;
        }

        $expiry = filemtime($file) + $this->duration;

        $this->log(sprintf(
            'Validate | File expiry: [%s] | Current time: [%s].',
            $expiry,
            time()
        ));

        return $expiry > time();
    }

    public function getCacheFile() : string
    {
        return Application::getTempFile(sprintf(
            'request-%s.cache',
            $this->request->getCacheHash()
        ));
    }

    /**
     * @param bool $enabled
     * @param int $durationSeconds
     * @return $this
     */
    public function setEnabled(bool $enabled, int $durationSeconds=0)
    {
        $this->enabled = $enabled;
        $this->duration = $durationSeconds;
        return $this;
    }

    /**
     * Stores the response in the cache.
     *
     * @param Connectors_Response $response
     * @throws FileHelper_Exception
     */
    public function storeResponse(Connectors_Response $response)
    {
        if(!$this->isEnabled())
        {
            $this->log('Store response | Ignoring, caching is not enabled.');
            return;
        }

        if($response->isError())
        {
            $this->log('Store response | Ignoring, the response has errors.');
            return;
        }

        $file = $this->getCacheFile();

        $this->log(sprintf('Store response | Saving to file [%s].', basename($file)));

        FileHelper::saveFile($file, $response->serialize());
    }

    public function fetchResponse() : Connectors_Response
    {
        $file = $this->getCacheFile();

        $this->log(sprintf('Fetch response | Loading the cache file [%s].', basename($file)));

        $data = FileHelper::readContents($file);

        return Connectors_Response::unserialize($this->request, $data);
    }
}
