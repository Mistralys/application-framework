<?php

require_once 'Application/Whatsnew/Version/Language.php';

class Application_Whatsnew_Version
{
    public const ERROR_UNKNOWN_LANGUAGE = 31201;
    
   /**
    * The version number.
    * @var string
    */
    protected $version;
    
   /**
    * @var Application_Whatsnew_Version_Language[]
    */
    protected $languages = array();

    /**
     * @var Application_Whatsnew
     */
    private $whatsnew;

    public function __construct(Application_Whatsnew $whatsnew, SimpleXMLElement $node)
    {
        $this->whatsnew = $whatsnew;
        $this->version = (string)$node['id'];

        $langIDs = Application_Whatsnew_Version_Language::getLanguageIDs();
        
        foreach($langIDs as $langID) 
        {
            if(!isset($node->$langID)) {
                continue;
            }

            $lang = new Application_Whatsnew_Version_Language($this, $langID, $node->$langID);
            if($lang->isValid()) {
                $this->languages[$langID] = $lang;
            }
        }
    }
    
   /**
    * @return string
    */
    public function getNumber() : string
    {
        return $this->version;
    }

    public function getWhatsnew() : Application_Whatsnew
    {
        return $this->whatsnew;
    }
    
    public function hasLanguage($langID)
    {
        return isset($this->languages[$langID]);
    }
    
    public function getLanguage($langID) : Application_Whatsnew_Version_Language
    {
        if(isset($this->languages[$langID])) {
            return $this->languages[$langID];
        }
        
        throw new Application_Exception(
            'Unknown language for version',
            sprintf(
                'Tried retrieving language [%s] for version [%s]. Available languages are [%s].',
                $langID,
                $this->getNumber(),
                implode(', ',array_keys($this->languages))
            ),
            self::ERROR_UNKNOWN_LANGUAGE
        );
    }
}