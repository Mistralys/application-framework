<?php

use Application\Application;
use Application\Ratings\Screens\RatingScreensCollection;
use AppUtils\ClassHelper;

/**
 * 
 * @method Application_Ratings_Rating getByID(int $ratingID)
 * @method Application_Ratings_Rating createNewRecord(array $data, bool $silent=false)
 */
class Application_Ratings extends DBHelper_BaseCollection
{
    public const int MAX_RATING = 5;
    
    public function getRecordClassName() : string
    {
        return Application_Ratings_Rating::class;
    }

    public function getRecordFiltersClassName() : string
    {
        return Application_Ratings_FilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return Application_Ratings_FilterSettings::class;
    }

    public function getRecordDefaultSortKey() : string
    {
        return 'label';
    }

    public function getRecordSearchableColumns() : array
    {
        return array(
            'comments' => t('Comments')
        );
    }

    public function getRecordTableName() : string
    {
        return 'app_ratings';
    }

    public function getRecordPrimaryName() : string
    {
        return 'rating_id';
    }

    public function getRecordTypeName() : string
    {
        return 'Screen rating';
    }

    public function getCollectionLabel() : string
    {
        return t('Application ratings');
    }

    public function getRecordLabel() : string
    {
        return t('Application screen rating');
    }

    public function getRecordProperties() : array
    {
        return array();
    }
    
    public function getMaxRating() : int
    {
        return self::MAX_RATING;
    }

    /**
     * @var array<int,string>|NULL
     */
    protected ?array $labels = null;
   
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
    
    public function injectJS(UI $ui) : void
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
     * @throws UI_Exception
     * @throws UI_Themes_Exception
     */
    public function renderWidget() : string
    {
        return UI::getInstance()
            ->getPage()
            ->renderTemplate(
                'frame.ratings',
                array(
                    'ratings' => $this,
                    'rating' => null
                )
            );
    }
  
   /**
    * Creates/gets an instance of the screen collection,
    * which is used to handle the application screens that
    * have been accessed by the rating system.
    * 
    * @return RatingScreensCollection
    */
    public function createScreens() : RatingScreensCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            RatingScreensCollection::class,
            DBHelper::createCollection(RatingScreensCollection::class)
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