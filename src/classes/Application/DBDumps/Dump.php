<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\FileHelper;

class Application_DBDumps_Dump
{
   /**
    * @var Application_DBDumps
    */
    protected $dumps;
    
   /**
    * @var int
    */
    protected $id;
    
   /**
    * The path to the dump file
    * @var string
    */
    protected $path;
    
    public function __construct(Application_DBDumps $dumps, int $id)
    {
        $this->dumps = $dumps;
        $this->id = $id;
        $this->path = $dumps->getDumpPath($id);
    }
    
    public function getPath() : string
    {
        return $this->path;
    }
    
    public function getID() : int
    {
        return $this->id;
    }
    
    public function getFileSize() : int
    {
        $size = filesize($this->path);
        if($size !== false) {
            return $size;
        }

        return 0;
    }
    
   /**
    * Retrieves a human readable label for the dump's file size.
    * @return string
    */
    public function getFileSizePretty() : string
    {
        return ConvertHelper::bytes2readable($this->getFileSize());
    }
    
   /**
    * Retrieves a human readable label for the dump's creation date.
    * @return string
    */
    public function getDatePretty() : string
    {
        return ConvertHelper::date2listLabel($this->getDateCreated(), true, true);
    }
    
   /**
    * @return DateTime
    */
    public function getDateCreated() : DateTime
    {
        return ConvertHelper::timestamp2date(filectime($this->path));
    }
    
   /**
    * Retrieves the URL to download the dump.
    * @return string
    */
    public function getURLDownload() : string
    {
        $req = Application_Request::getInstance();
        
        return $req->buildURL(array(
            'page' => 'devel',
            'mode' => 'dbdump',
            'dump_id' => $this->getID(),
            'download' => 'yes'
        ));
    }
    
   /**
    * Sends the dump file to the browser to be downloaded.
    * @return never
    */
    public function sendFile() : void
    {
        FileHelper::sendFile($this->path);
        
        Application::exit('Sent DB dump file.');
    }
    
    public function delete() : void
    {
        FileHelper::deleteFile($this->path);
    }
}
