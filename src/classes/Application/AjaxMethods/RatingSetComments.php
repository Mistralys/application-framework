<?php

use Application\AppFactory;

class Application_AjaxMethods_RatingSetComments extends Application_AjaxMethod
{
    public const METHOD_NAME = 'RatingSetComments';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $this->startTransaction();
        
        $this->rating->setComments($this->comments);
        $this->rating->save();
        
        $this->endTransaction();
        
        $payload = array(
            'rating_id' => $this->rating->getID(),
            'comments' => $this->rating->getComments(),
            'date' => $this->rating->getDate()->format('Y-m-d H:i:s'),
            'rating' => $this->rating->getRating()
        );
        
        return $this->sendResponse($payload);
    }
    
   /**
    * @var Application_Ratings_Rating
    */
    protected $rating;
    
   /**
    * @var Application_Ratings
    */
    protected $ratings;
    
   /**
    * @var string
    */
    protected $comments;
    
    protected function validateRequest() : void
    {
        $this->ratings = AppFactory::createRatings();
        
        $this->comments = $this->request->getParam('comments');
        if(empty($this->comments)) {
            $this->sendErrorUnknownElement(t('comments'));
        }
        
        $ratingID = $this->request->registerParam('rating_id')->setCallback(array($this->ratings, 'idExists'))->get();
        if(empty($ratingID)) {
            $this->sendErrorUnknownElement(t('rating'));
        }
        
        $this->rating = $this->ratings->getByID($ratingID);
    }
}