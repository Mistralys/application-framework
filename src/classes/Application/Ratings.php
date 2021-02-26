<?php

/**
 * 
 * @method Application_Ratings_Rating getByID(int $ratingID)
 * @method Application_Ratings_Rating createNewRecord(array $data, bool $silent=false)
 */
class Application_Ratings extends DBHelper_BaseCollection
{
    const MAX_RATING = 5;
    
    public function getRecordClassName()
    {
        return 'Application_Ratings_Rating';
    }

    public function getRecordFiltersClassName()
    {
        return 'Application_Ratings_FilterCriteria';        
    }

    public function getRecordFilterSettingsClassName()
    {
        return 'Application_Ratings_FilterSettings';
    }

    public function getRecordDefaultSortKey()
    {
        return 'label';
    }

    public function getRecordSearchableColumns()
    {
        return array(
            'comments'
        );
    }

    public function getRecordTableName()
    {
        return 'app_ratings';
    }

    public function getRecordPrimaryName()
    {
        return 'rating_id';
    }

    public function getRecordTypeName()
    {
        return 'Screen rating';
    }

    public function getCollectionLabel()
    {
        return t('Application ratings');
    }

    public function getRecordLabel()
    {
        return t('Application screen rating');
    }

    public function getRecordProperties()
    {
        return array();
    }
    
    public function getMaxRating() : int
    {
        return self::MAX_RATING;
    }
   
    protected $labels;
   
    public function getRatingLabel($rating)
    {
        if(!isset($this->labels)) {
            $this->labels = array(
                1 => t('It\'s broken.'),
                2 => t('It\'s rather bad.'),
                3 => t('It\'s okay.'),
                4 => t('It\'s good.'),
                5 => t('It works like a charm.')
            );
        }
        
        return $this->labels[$rating];
    }
    
    public function injectJS(UI $ui)
    {
        $ui->addStylesheet('ui-ratings.css');
        $ui->addJavascript('ratings.js');
        
        $ui->addJavascriptHeadVariable('Ratings.MaxRating', self::MAX_RATING);
        $ui->addJavascriptOnload('Ratings.Start()');
    }
    
   /**
    * Renders the HTML code for the rating widget in the user interface.
    * 
    * @return string
    * @template frame.ratings
    */
    public function renderWidget() : string
    {
        $page = UI::getInstance()->getPage();
        
        return $page->renderTemplate(
            'frame.ratings',
            array(
                'ratings' => $this,
                'rating' => null
            )
        );
    }
  
   /**
    * Creates/gets an instance of the screens collection,
    * which is used to handle the application screens that
    * have been accessed by the rating system.
    * 
    * @return Application_Ratings_Screens
    */
    public function createScreens() : Application_Ratings_Screens
    {
        return ensureType(
            Application_Ratings_Screens::class,
            DBHelper::createCollection(Application_Ratings_Screens::class)
        );
    }
    
   /**
    * Adds a new rating for the current user and the specified source screen URL.
    * 
    * @param string $screenURL
    * @param int $rating
    * @return Application_Ratings_Rating
    */
    public function addRating(string $screenURL, int $rating) : Application_Ratings_Rating
    {
        $screen = $this->createScreens()->createNewByURL($screenURL);
        
        $data = array(
            'user_id' => Application::getUser()->getID(),
            'rating' => $rating,
            'date' => date('Y-m-d H:i:s'),
            'rating_screen_id' => $screen->getID(),
            'comments' => '',
            'app_version' => Application_Driver::getBuildNumber()
        );
        
        return $this->createNewRecord($data);
    }
}