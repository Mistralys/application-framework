<?php
/**
 * @package Connectors
 * @supackage Request
 * @see Connectors_Request_Cache
 */

declare(strict_types=1);

use Application\Application;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Handles caching information for a request.
 *
 * @package Connectors
 * @supackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Request_Cache implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private Connectors_Request $request;
    private bool $enabled = false;
    private int $duration = 0;

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
        if(!$this->isEnabled())
        {
            return false;
        }

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
    public function setEnabled(bool $enabled, int $durationSeconds=0) : self
    {
        $this->enabled = $enabled;
        $this->duration = $durationSeconds;
        return $this;
    }

    /**
     * Stores the response in the cache.
     *
     * Uses an atomic write strategy: the serialized data is
     * first written to a temporary file, then renamed to the
     * target path. On POSIX systems, {@see rename()} is atomic,
     * which prevents concurrent readers from seeing partial writes.
     *
     * @param Connectors_Response $response
     * @throws FileHelper_Exception
     * @return $this
     */
    public function storeResponse(Connectors_Response $response) : self
    {
        if(!$this->isEnabled())
        {
            $this->log('Store response | Ignoring, caching is not enabled.');
            return $this;
        }

        if($response->isError())
        {
            $this->log('Store response | Ignoring, the response has errors.');
            return $this;
        }

        $file = $this->getCacheFile();

        $this->log(sprintf('Store response | Saving to file [%s].', basename($file)));

        // Write to a temporary file first, then rename atomically
        // to prevent concurrent readers from seeing partial writes.
        $tempFile = $file.'.'.getmypid().'.tmp';

        FileHelper::saveFile($tempFile, $response->serialize());

        if(!rename($tempFile, $file))
        {
            $this->log(sprintf('Store response | Atomic rename failed for [%s], cleaning up temp file.', basename($file)));
            @unlink($tempFile);
        }

        return $this;
    }

    public function fetchResponse() : ?Connectors_Response
    {
        $file = $this->getCacheFile();

        $this->log(sprintf('Fetch response | Loading the cache file [%s].', basename($file)));

        $response = Connectors_Response::unserialize(FileHelper::readContents($file));

        if($response === null)
        {
            $this->log(sprintf('Fetch response | Corrupt cache file [%s], deleting.', basename($file)));
            @unlink($file);
        }

        return $response;
    }
}
