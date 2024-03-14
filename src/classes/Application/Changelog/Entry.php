<?php

use Application\Interfaces\ChangelogableInterface;
use AppUtils\ConvertHelper;

class Application_Changelog_Entry
{
    protected ChangelogableInterface $owner;
    protected int $id;
    protected int $authorID;
    protected string $date;
    protected string $type;
    protected string $data;
    protected ?string $text = null;

   /**
    * @var array<string,mixed>|NULL
    */
    protected ?array $dataDecoded = null;
    
    public function __construct(ChangelogableInterface $owner, int $id, int $authorID, string $type, string $date, string $data)
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
    public function getID() : int
    {
        return $this->id;
    }
    
   /**
    * Human-readable text describing the change that was made
    * in this changelog entry.
    * 
    * @return string
    */
    public function getText() : string
    {
        if(!isset($this->text)) {        
            $this->text = $this->owner->getChangelogEntryText($this->type, $this->getData());
        }
        
        return $this->text;
    }
    
    public function getType() : string
    {
        return $this->type;
    }
    
   /**
    * Retrieves the data set stored with the changelog entry.
    * @return array<string,mixed>
    */
    public function getData() : array
    {
        if(!isset($this->dataDecoded))
        {
            $this->dataDecoded = json_decode($this->data, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->dataDecoded;
    }
    
    protected ?DateTime $dateDecoded = null;
    
   /**
    * Retrieves the date at which the changelog entry was added.
    * @return DateTime
    */
    public function getDate() : DateTime
    {
        if(!isset($this->dateDecoded)) {
            $this->dateDecoded = new DateTime($this->date);
        }
        
        return $this->dateDecoded;
    }
    
    public function getDatePretty(bool $includeTime=false) : string
    {
        return ConvertHelper::date2listLabel($this->getDate(), $includeTime, true);
    }
    
   /**
    * The ID of the user who authored the changelog entry.
    * @return integer
    */
    public function getAuthorID() : int
    {
        return $this->authorID;
    }
    
    protected ?Application_User $author = null;
    
   /**
    * Retrieves the user object for the author of the changelog entry.
    * @return Application_User
    */
    public function getAuthor() : Application_User
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
    
    public function getAuthorName() : string
    {
        return $this->getAuthor()->getName();
    }

    /**
     * @return mixed|null
     * @throws JsonException
     */
    public function getValueBefore()
    {
        return $this->getDiffValue('before');
    }

    /**
     * @return mixed|null
     * @throws JsonException
     */
    public function getValueAfter()
    {
        return $this->getDiffValue('after');
    }

    /**
     * @var array<mixed>|null
     */
    protected ?array $diff = null;

    /**
     * @param string $part
     * @return mixed|null
     * @throws JsonException
     */
    protected function getDiffValue(string $part)
    {
        if(!isset($this->diff)) {
            $this->diff = $this->owner->getChangelogEntryDiff($this->type, $this->getData());
        }
    
        return $this->diff[$part] ?? null;
    }
    
    public function hasDiff() : bool
    {
        $this->getDiffValue('before');
        return is_array($this->diff);
    }
}