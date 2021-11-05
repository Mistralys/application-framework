<?php

declare(strict_types=1);

class Application_Whatsnew
{
    public const ERROR_WHATSNEW_FILE_NOT_FOUND = 30001;
    public const ERROR_COULD_NOT_PARSE_XML = 30002;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var Parsedown
     */
    private $parsedown;

    /**
     * @var Application_Whatsnew_Version[]
     */
    protected $versions = array();

    public function __construct()
    {
        $this->file = APP_ROOT . '/WHATSNEW.xml';
        $this->parsedown = new Parsedown();

        if(!file_exists($this->file)) {
            throw new Application_Exception(
                sprintf('Could not find file [%s].', basename($this->file)),
                '',
                self::ERROR_WHATSNEW_FILE_NOT_FOUND
            );
        }
        
        $this->parse();
    }

    /**
     * @return Parsedown
     */
    public function getParsedown(): Parsedown
    {
        return $this->parsedown;
    }

    protected function parse() : void
    {
        $xml = simplexml_load_file($this->file);
        
        if($xml === false) {
            throw new Application_Exception(
                sprintf('Could not read file [%s].', basename($this->file)),
                'Trying to parse the XML failed. Syntax error?',
                self::ERROR_COULD_NOT_PARSE_XML
            );
        }
        
        foreach ($xml->version as $versionNode) {
            $this->versions[] = new Application_Whatsnew_Version($this, $versionNode);
        }
    }
    
   /**
    * Retrieves the current (highest) version.
    * 
    * @return Application_Whatsnew_Version|NULL
    */
    public function getCurrentVersion() : ?Application_Whatsnew_Version
    {
        if(!empty($this->versions)) {
            return $this->versions[0];
        }
        
        return null;
    }
    
   /**
    * @return Application_Whatsnew_Version[]
    */
    public function getVersions() : array
    {
        return $this->versions;
    }
    
   /**
    * @param string $langID
    * @return Application_Whatsnew_Version[]
    */
    public function getVersionsByLanguage(string $langID) : array
    {
        $result = array();
        foreach($this->versions as $version) {
            if($version->hasLanguage($langID)) {
                $result[] = $version;
            }
        }
        
        return $result;
    }
}