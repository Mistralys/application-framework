<?php

use Application\AppFactory;

class Application_AjaxMethods_RatingAdd extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'RatingAdd';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $this->startTransaction();
        
        $rating = $this->ratings->addRating($this->sourceURL, $this->rating);
        
        $this->endTransaction();
        
        $payload = array(
            'rating_id' => $rating->getID(),
            'comments' => $rating->getComments(),
            'date' => $rating->getDate()->format('Y-m-d H:i:s'),
            'rating' => $rating->getRating()
        );
        
        return $this->sendResponse($payload);
    }
    
   /**
    * @var string
    */
    protected $sourceURL;
    
   /**
    * @var int
    */
    protected $rating;
    
   /**
    * @var Application_Ratings
    */
    protected $ratings;
    
    protected function validateRequest()
    {
        $this->sourceURL = $this->request->registerParam('source_url')->setURL()->get();
        if(empty($this->sourceURL)) {
            $this->sendErrorUnknownElement(t('URL'));
        }
        
        $this->rating = $this->request->registerParam('rating')->setInteger()->get();
        if(empty($this->rating)) {
            $this->sendErrorUnknownElement(t('rating'));
        }
        
        $this->ratings = AppFactory::createRatings();
    }
}