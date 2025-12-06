<?php
/**
 * File containing the {@see Application_Ratings_Rating} class.
 * 
 * @package Application
 * @subpackage Ratings
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Ratings_Rating
 */

/**
 * @package Application
 * @subpackage Ratings
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @property Application_Ratings $collection
 * @method Application_Ratings getCollection()
 */
class Application_Ratings_Rating extends DBHelper_BaseRecord
{
    
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }
    
    public function getRating() : int
    {
        return $this->getRecordIntKey('rating');
    }
    
    public function getComments() : string
    {
        return $this->getRecordStringKey('comments');
    }
    
    public function setComments(string $comments) : Application_Ratings_Rating
    {
        $this->setRecordKey('comments', $comments);
        return $this;
    }
    
    protected ?RatingScreenRecord $screen = null;
    
   /**
    * 
    * @return RatingScreenRecord
    */
    public function getScreen() : RatingScreenRecord
    {
        if(!isset($this->screen)) {
            $this->screen = $this->collection->createScreens()->getByID($this->getScreenID());
        }
        
        return $this->screen;
    }
    
    public function getDate() : ?DateTime
    {
        return $this->getRecordDateKey('date');
    }
    
    public function getScreenID() : int
    {
        return $this->getRecordIntKey('rating_screen_id');
    }

    public function getLabel() : string
    {
        return $this->getScreen()->getLabel().' '.$this->getRating();   
    }
}
