<?php

class Application_Changelog_Entry
{
   /**
    * @var Application_Changelogable_Interface
    */
    protected $owner;
    
    protected $id;
    
    protected $authorID;
    
    protected $date;
    
   /**
    * @var string
    */
    protected $type;
    
    protected $data; 
    
   /**
    * @var array<string,mixed>
    */
    protected $dataDecoded = array();
    
    private $isDataDecoded = false;
    
    protected $text;
    
    public function __construct(Application_Changelogable_Interface $owner, $id, $authorID, string $type, $date, $data)
    {
        $this->owner = $owner;
        $this->id = $id;
        $this->authorID = $authorID;
        $this->date = $date;
        $this->type = $type;
        $this->data = $data;
    }
    
   /**
    * @return integer
    */
    public function getID()
    {
        return $this->id;
    }
    
   /**
    * Human readable text describing the change that was made
    * in this changelog entry.
    * 
    * @return string
    */
    public function getText()
    {
        if(!isset($this->text)) {        
            $this->text = $this->owner->getChangelogEntryText($this->type, $this->getData());
        }
        
        return $this->text;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
   /**
    * Retrieves the data set stored with the changelog entry.
    * @return array<string,mixed>
    */
    public function getData()
    {
        if(!$this->isDataDecoded) 
        {
            $this->dataDecoded = json_decode($this->data, true);
            $this->isDataDecoded = true;
        }       

        return $this->dataDecoded;
    }
    
    protected $dateDecoded;
    
   /**
    * Retrieves the date at which the changelog entry was added.
    * @return DateTime
    */
    public function getDate()
    {
        if(!isset($this->dateDecoded)) {
            $this->dateDecoded = new DateTime($this->date);
        }
        
        return $this->dateDecoded;
    }
    
    public function getDatePretty($includeTime=false)
    {
        return AppUtils\ConvertHelper::date2listLabel($this->getDate(), $includeTime, true);
    }
    
   /**
    * The ID of the user who authored the changelog entry.
    * @return integer
    */
    public function getAuthorID()
    {
        return $this->authorID;
    }
    
   /**
    * @var Application_User
    */
    protected $author;
    
   /**
    * Retrieves the user object for the author of the changelog entry.
    * @return Application_User
    */
    public function getAuthor()
    {
        if(isset($this->author)) 
        {
            return $this->author;
        }
        
        if(Application::userIDExists($this->authorID)) 
        {
            $this->author = Application::createUser($this->authorID);
        }
        else
        {
            $this->author = Application::createSystemUser();
        }
        
        return $this->author;
    }
    
    public function getAuthorName()
    {
        return $this->getAuthor()->getName();
    }
    
    public function getValueBefore()
    {
        return $this->getDiffValue('before');
    }
    
    public function getValueAfter()
    {
        return $this->getDiffValue('after');
    }
    
    protected $diff;
    
    protected $diffLoaded = false;
    
    protected function getDiffValue($part)
    {
        if(!$this->diffLoaded) {
            $this->diff = $this->owner->getChangelogEntryDiff($this->type, $this->getData());
            $this->diffLoaded = true;
        }
    
        if(isset($this->diff) && isset($this->diff[$part])) {
            return $this->diff[$part];
        }
    
        return null;
    }
    
    public function hasDiff()
    {
        $this->getDiffValue('before');
        return is_array($this->diff);
    }
}