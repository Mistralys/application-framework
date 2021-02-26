<?php
/**
 * File containing the {@link Application_Ratings_Screens} class.
 * 
 * @package Application
 * @subpackage Ratings
 * @see Application_Ratings_Screens
 */

/**
 * Collection manager for the rating screens that users have accessed:
 * each time a rating is added, the details on the requested page are
 * stored in the global screens list. The user's rating only references
 * the screen entry.
 *  
 * @package Application
 * @subpackage Ratings
 * @author Sebastian Mordziol <s.mordziol@gmail.com>
 * 
 * @method Application_Ratings_Screens_Screen getByID(int $screenID)
 * @method Application_Ratings_Screens_Screen createNewRecord(array $data, bool $silent=false)
 */
class Application_Ratings_Screens extends DBHelper_BaseCollection
{
    public function getRecordClassName()
    {
        return 'Application_Ratings_Screens_Screen';
    }

    public function getRecordFiltersClassName()
    {
        return 'Application_Ratings_Screens_FilterCriteria';
    }

    public function getRecordFilterSettingsClassName()
    {
        return 'Application_Ratings_Screens_FilterSettings';
    }

    public function getRecordDefaultSortKey()
    {
        return 'path';   
    }

    public function getRecordSearchableColumns()
    {
        return array(
            'path',
            'dispatcher',
            'params'
        );
    }

    public function getRecordTableName()
    {
        return 'app_ratings_screens';
    }

    public function getRecordPrimaryName()
    {
        return 'rating_screen_id';
    }

    public function getRecordTypeName()
    {
        return 'Application rating screen';
    }

    public function getCollectionLabel()
    {
        return 'Application rating screens';
    }

    public function getRecordLabel()
    {
        return 'Application rating screen';
    }

    public function getRecordProperties()
    {
        return array();
    }

    /**
     * Creates a screen instance for the target URL. Automatically creates
     * a new screen entry in the database if it does not exist yet.
     *
     * @param string $screenURL
     * @return Application_Ratings_Screens_Screen
     */
    public function createNewByURL(string $screenURL) : Application_Ratings_Screens_Screen
    {
        $info = Application_Driver::getInstance()->parseURL($screenURL);
        $hash = $info->getHash();
        
        $screen = $this->getByHash($hash);
        if($screen) {
            return $screen;
        }
        
        $data = array(
            'hash' => $hash,
            'dispatcher' => $info->getDispatcher(),
            'path' => $info->getScreenPath(),
            'params' => ''
        );
        
        if($info->hasParams()) {
            $data['params'] = json_encode($info->getParams());
        }
        
        return $this->createNewRecord($data);
    }

    /**
     * Attempts to retrieve a screen entry by an URL hash.
     *
     * @param string $hash
     * @return Application_Ratings_Screens_Screen|NULL
     */
    public function getByHash($hash) : ?Application_Ratings_Screens_Screen
    {
        $id = DBHelper::fetchKeyInt(
            'rating_screen_id',
            "SELECT
                rating_screen_id
            FROM
                app_ratings_screens
            WHERE
                hash=:hash",
            array(
                'hash' => $hash
            )
        );
        
        if(!empty($id)) {
            return $this->getByID($id);
        }
        
        return null;
    }
}