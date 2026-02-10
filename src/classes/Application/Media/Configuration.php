<?php

use Application\Application;

abstract class Application_Media_Configuration
{
    public const ERROR_UNKNOWN_MEDIA_CONFIGURATION = 690001;
    
    public const ERROR_DATASET_TYPE_MISMATCH = 690002;
  
    public const ERROR_INVALID_DOCUMENT_TYPE = 690003;
    
    protected $cachedTypeID;

    protected $data = array();
    
    public function getTypeID()
    {
        if(!isset($this->cachedTypeID)) {
            $this->cachedTypeID = str_replace('Application_Media_Configuration_', '', get_class($this));
        }
        
        return $this->cachedTypeID;
    }
    
    protected $cachedID;
    
    public function getID()
    {
        if(isset($this->cachedID)) {
            return $this->cachedID;
        }
        
        $data = json_encode($this->data);
        $key = md5($data);

        $id = DBHelper::fetchKey(
            'config_id', 
            "SELECT
                `config_id`
            FROM
                `media_configurations`
            WHERE
                `config_key`=:config_key",
            array(
                ':config_key' => $key
            )
        );
        
        if(is_null($id)) {
            $id = DBHelper::insert(
                "INSERT INTO
                    `media_configurations`
                SET
                    `type_id`=:type_id,
                    `config_key`=:config_key,
                    `config`=:config",
                array(
                    'type_id' => $this->getTypeID(),
                    'config_key' => $key,
                    'config' => $data 
                )
            );
        }

        $this->cachedID = $id;
        return $id;
    }
    
    protected function getData($part, $default=null)
    {
        if(isset($this->data[$part])) {
            return $this->data[$part];
        }
        
        return $default;
    }
    
    protected function setData($name, $value)
    {
        $this->data[$name] = $value;
        $this->cachedID = null; // force ID refresh after changes
    }
    
   /**
    * Checks whether the specified document has to be pre-processed.
    * @param Application_Media_Document $document
    */
    abstract public function isProcessRequired(Application_Media_Document $document);
    
    protected function log($message)
    {
        Application::log(sprintf(
            '%s media configuration | %s',
            $this->getTypeID(),
            $message
        ));
    }
    
   /**
    * Makes the configuration load a stored configuration from
    * the database. The document type has to match of course.
    * 
    * @param integer $config_id
    * @throws Application_Exception
    */
    public function loadData($config_id)
    {
        $data = DBHelper::fetch(
            "SELECT
                `type_id`,
                `config`
            FROM
                `media_configurations`
            WHERE
                `config_id`=:config_id",
            array(
                'config_id' => $config_id
            )
        );
        
        if(!is_array($data) || !isset($data['type_id'])) {
            throw new Application_Exception(
                'Unknown media configuration',
                sprintf(
                    'Could not retrieve the media configuration [%s] from the database.',
                    $config_id
                ),
                self::ERROR_UNKNOWN_MEDIA_CONFIGURATION
            );
        }
        
        if($data['type_id'] != $this->getTypeID()) {
            throw new Application_Exception(
                'Invalid media type',
                sprintf(
                    'Cannot load [%s] configuration from ID [%s], the type [%s] does not match.',
                    $this->getTypeID(),
                    $config_id,
                    $data['type_id']    
                ),
                self::ERROR_DATASET_TYPE_MISMATCH
            );
        }
        
        $this->data = json_decode($data['config'], true);
        $this->cachedID = $config_id;
    }
    
   /**
    * Processes the media document using the configuration:
    * goes through all required pre-processing steps required
    * by the document type. For images for example, this will
    * create all resized versions for the available presets.
    * 
    * @param Application_Media_Document $document
    */
    abstract public function process(Application_Media_Document $document);
    
    protected function requireMatchingType(Application_Media_Document $document)
    {
        if($document->getTypeID() == $this->getTypeID()) {
            return;
        }
        
        throw new Application_Exception(
            'Media configuration error',
            sprintf(
                'The media configuration of type [%s] cannot check the processing state for documents of type [%s].',
                $this->getTypeID(),
                $document->getTypeID()
            ),
            self::ERROR_INVALID_DOCUMENT_TYPE
        );
    }
}