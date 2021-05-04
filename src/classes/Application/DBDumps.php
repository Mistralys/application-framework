<?php

class Application_DBDumps
{
   /**
    * @var Application_Driver
    */
    protected $driver;
    
   /**
    * @var string
    */
    protected $storagePath;
    
    public function __construct(Application_Driver $driver)
    {
        $this->driver = $driver;
        $this->storagePath = Application::getStorageSubfolderPath('dumps');
    }
    
   /**
    * Retrieves the path to the folder in which database dumps are stored.
    * @return string
    */
    public function getStoragePath()
    {
        return $this->storagePath;
    }
    
   /**
    * Creates a new incremental DB dump in the global database
    * dumps folder of the application. If the folder does not
    * exist, will attempt to create it.
    *
    * @return Application_DBDumps_Dump 
    * @throws Application_Exception
    */
    public function createDump() : Application_DBDumps_Dump
    {
        $id = (int)$this->driver->getSetting('db-dump-idcounter', '1') + 1;
        $this->driver->setSetting('db-dump-idcounter', (string)$id);

        $cmd = null;
        
        if(isOSWindows()) 
        {
            $cmd = sprintf(
                'mysqldump.exe --default-character-set=utf8 --single-transaction -u %1$s -h %2$s -p%3$s %4$s > %5$s',
                APP_DB_USER,
                APP_DB_HOST,
                APP_DB_PASSWORD,
                APP_DB_NAME,
                $this->getDumpPath($id)
            );
        }
        else
        {
            $cmd = sprintf(
                'mysqldump --default-character-set=utf8 --single-transaction -u %1$s -h %2$s -p%3$s %4$s | gzip > %5$s',
                APP_DB_USER,
                APP_DB_HOST,
                APP_DB_PASSWORD,
                APP_DB_NAME,
                $this->getDumpPath($id)
            );
        }
        
        system($cmd);
        
        return $this->getByID($id);
    }
    
   /**
    * Checks whether a dump with the specified ID exists in the file system.
    * @param int $id
    * @return boolean
    */
    public function dumpExists($id)
    {
        return file_exists($this->getDumpPath($id));
    }
    
   /**
    * Retrieves a dump instance. Will throw an exception if
    * it does not exist - always check beforehand if it does.
    * 
    * @param int $id
    * @return Application_DBDumps_Dump
    */
    public function getByID(int $id) : Application_DBDumps_Dump
    {
        return new Application_DBDumps_Dump($this, $id);
    }
    
   /**
    * Gets the path to the dump file for the specified ID.
    * @param int $id
    * @return string
    */
    public function getDumpPath(int $id) : string
    {
        
        return sprintf(
            '%s/%d.%s',
            $this->storagePath,
            $id,
            $this->getExtension()
        );
    }
    
    public function getExtension() : string
    {
        if(isOSWindows()) {
            return 'sql';
        }
        
        return 'gz';
    }
    
   /**
    * Retrieves all currently available DB dumps stored
    * in the dumps folder.
    * 
    * @return Application_DBDumps_Dump[]
    */
    public function getAll()
    {
        $files = AppUtils\FileHelper::findFiles(
            $this->storagePath, 
            array(
                $this->getExtension()
            ),
            array(
                'strip-extension' => true
            )
        );
        
        $result = array();
        foreach($files as $id) {
            $result[] = $this->getByID(intval($id));
        }
        
        usort($result, array($this, 'callback_sortDumps'));
        
        return $result;
    }
    
    public function callback_sortDumps(Application_DBDumps_Dump $a, Application_DBDumps_Dump $b)
    {
        if($a->getDateCreated() > $b->getDateCreated()) {
            return -1;
        }
        
        if($a->getDateCreated() < $b->getDateCreated()) {
            return 1;
        }
        
        return 0;
    }
}
