<?php

require_once 'Application/Whatsnew/Version/Language/Category.php';

class Application_Whatsnew_Version_Language
{
    const ERROR_UNKNOWN_LANGUAGE_ID = 30101;

    protected static $languages = array(
        'de' => array(
            'miscLabel' => 'Sonstiges',
            'develOnly' => false
        ),
        'en' => array(
            'miscLabel' => 'Miscellaneous',
            'develOnly' => false
        ),
        'dev' => array(
            'miscLabel' => 'Developer',
            'develOnly' => true
        )
    );

    /**
     * @var Application_Whatsnew_Version
     */
    protected $version;

    /**
     * @var string
     */
    protected $langID;

    public function __construct(Application_Whatsnew_Version $version, string $langID, SimpleXMLElement $node)
    {
        if (!isset(self::$languages[$langID])) {
            throw new Application_Exception(
                sprintf('Unknown language [%s].', $langID),
                sprintf('Known languages are [%s].', implode(', ', $this->getLanguageIDs())),
                self::ERROR_UNKNOWN_LANGUAGE_ID
            );
        }

        $this->version = $version;
        $this->langID = $langID;

        $this->parse($node);
    }

    public function getWhatsnew(): Application_Whatsnew
    {
        return $this->version->getWhatsnew();
    }
    
    public function getID()
    {
        return $this->langID;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public static function getLanguageIDs()
    {
        return array_keys(self::$languages);
    }
    
    public function isValid()
    {
        if(!$this->isDeveloperOnly() || Application::getUser()->isDeveloper()) {
            return true;
        }
        
        return false;
    }
    
    public function isDeveloperOnly()
    {
        return self::$languages[$this->langID]['develOnly'];
    }
    
    public function getMiscLabel()
    {
        return self::$languages[$this->langID]['miscLabel'];
    }
    
   /**
    * @var Application_Whatsnew_Version_Language_Category[]
    */
    protected $categories = array();
    
    protected function parse(SimpleXMLElement $node)
    {
        foreach($node->item as $itemNode)
        {
            $categoryLabel = (string)$itemNode['category'];
            if(empty($categoryLabel)) {
                $categoryLabel = $this->getMiscLabel();
            }
            
            $category = $this->getCategoryByLabel($categoryLabel);
            $category->addItem($itemNode);
        }
        
        ksort($this->categories);
    }
    
    public function getCategoryByLabel($label)
    {
        if(!isset($this->categories[$label])) {
            $this->categories[$label] = new Application_Whatsnew_Version_Language_Category($this, $label);
        }
        
        return $this->categories[$label];
    }

   /**
    * @return Application_Whatsnew_Version_Language_Category[]
    */
    public function getCategories()
    {
        return array_values($this->categories);
    }
    
    public function toArray()
    {
        $result = array();
        
        foreach($this->categories as $category) {
            $result[] = $category->toArray();
        }
        
        return $result;
    }
}