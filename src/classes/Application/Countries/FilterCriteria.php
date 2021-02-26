<?php 
/**
 * File containing the {@link Application_Countries_FilterCriteria} class.
 * 
 * @package Maileditor
 * @subpackage Countries
 * @see Application_Countries_FilterCriteria
 */

declare(strict_types=1);

/**
 * Filter criteria handler for countries.
 * 
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method Application_Countries_Country[] getItemsObjects()
 */
class Application_Countries_FilterCriteria extends DBHelper_BaseFilterCriteria
{
   /**
    * @var bool
    */
    protected $excludeInvariant = false;
    
    protected function prepareQuery()
    {
        $this->addWhereColumnIN('`country_id`', $this->getCriteriaValues('country_ids'));
        
        if($this->excludeInvariant) 
        {
            $this->addWhere('`country_id` != '.Application_Countries_Country::COUNTRY_INDEPENDENT_ID);
        }
    }
    
   /**
    * Limits the list to the specified country IDs.
    * @param integer[] $ids
    * @return Application_Countries_FilterCriteria
    */
    public function selectCountryIDs(array $ids) : Application_Countries_FilterCriteria
    {
        if(empty($ids)) {
            $ids = array(0);
        }
        
        foreach($ids as $country_id) 
        {
            $this->selectCountryID($country_id);
        }
        
        return $this;
    }
    
   /**
    * Limits the list to the specified country by its ID.
    * @param integer $country_id
    * @return Application_Countries_FilterCriteria
    */
    public function selectCountryID(int $country_id) : Application_Countries_FilterCriteria
    {
        return $this->selectCriteriaValue('country_ids', $country_id);
    }
    
   /**
    * Excludes the invariant country from the results.
    * 
    * @param bool $exclude
    * @return Application_Countries_FilterCriteria
    */
    public function excludeInvariant(bool $exclude=true) : Application_Countries_FilterCriteria
    {
        $this->excludeInvariant = $exclude;
        
        return $this;
    }
}
