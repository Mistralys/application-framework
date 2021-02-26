<?php

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
    
    public function __construct(Application_DBDumps $dumps, $id)
    {
        $this->dumps = $dumps;
        $this->id = $id;
        $this->path = $dumps->getDumpPath($id);
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getID()
    {
        return $this->id;
    }
    
    public function getFileSize()
    {
        return filesize($this->path);
    }
    
   /**
    * Retrieves a human readable label for the dump's file size.
    * @return string
    */
    public function getFileSizePretty()
    {
        return AppUtils\ConvertHelper::bytes2readable($this->getFileSize());
    }
    
   /**
    * Retrieves a human readable label for the dump's creation date.
    * @return string
    */
    public function getDatePretty()
    {
        return AppUtils\ConvertHelper::date2listLabel($this->getDateCreated(), true, true);
    }
    
   /**
    * @return DateTime
    */
    public function getDateCreated()
    {
        return AppUtils\ConvertHelper::timestamp2date(filectime($this->path));
    }
    
   /**
    * Retrieves the URL to download the dump.
    * @return string
    */
    public function getURLDownload()
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
    */
    public function sendFile()
    {
        AppUtils\FileHelper::sendFile($this->path);
        
        Application::exit('Sent DB dump file.');
    }
    
    public function delete()
    {
        return unlink($this->path);
    }
}
