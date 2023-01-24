<?php

declare(strict_types=1);

class Application_Media_Delivery implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;
    
    private static ?Application_Media_Delivery $instance = null;

   /**
    * The full path to the storage folder.
    * @see getStorageFolder()
    * @var string
    */
    private string $storageFolder;

    private Application_Request $request;
    
    /**
     * Retrieves the global instance of the media manager. Creates
     * the instance as needed.
     *
     * @return Application_Media_Delivery
     */
    public static function getInstance() : Application_Media_Delivery
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application_Media_Delivery();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->storageFolder = Application::getStorageSubfolderPath('cache');
        $this->request = Application_Driver::getInstance()->getRequest();
    }

    /**
     * Serves a media file from the current request: checks
     * the request variables for the required parameters and
     * retrieves the matching media file, then serves it.
     */
    public function serveFromRequest()
    {
        $esales = Application_Driver::getInstance();
        $request = $esales->getRequest();

        try 
        {
            switch($request->getParam('source')) 
            {
                case 'media': $this->serveMedia(); break;
                case 'upload': $this->serveUpload(); break;
            }
        } 
        catch(Exception $e) 
        {
            if(Application::isSimulation())
            {
                displayError($e);
            }
            else
            {
                $this->sendError('Exception: ' . $e->getMessage());
            }
        }
        
        $this->sendError('Invalid source');
    }
    
    private function serveMedia() : void
    {
        $media_id = $this->request->getParam('media_id');
        
        $media = Application_Media::getInstance();
        
        if(!$media->idExists($media_id))
        {
            $this->logError(sprintf('Media document with ID [%s] not found in database.', $media_id));
            
            $this->sendError('Media document not found');
        }
        
        $media->getByID($media_id)->serveFromRequest($this, $this->request);
    }
    
    private function serveUpload() : void
    {
        $upload_id = $this->request->getParam('upload_id');
        
        $uploads = Application_Uploads::getInstance();
        
        if(!$uploads->idExists($upload_id))
        {
            $this->logError(sprintf('Upload with ID [%s] not found in database.', $upload_id));
            
            $this->sendError('Upload not found');
        }
        
        $uploads->getByID($upload_id)->serveFromRequest($this, $this->request);
    }

    public function sendError(string $title) : void
    {
        $this->logError('Cannot send media: '.$title);
        
        if(!Application::isSimulation())
        {
            header('HTTP/1.1 400 Bad Request: ' . $title, true, 400);
        }
        
        Application::exit();
    }
    
    public function getLogIdentifier(): string
    {
        return 'Media Delivery';
    }
}
