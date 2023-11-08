<?php
/**
 * @package Application
 * @subpackage Countries
 * @see Application_Countries_Selector
 */

declare(strict_types=1);

use Application\Countries\CountriesCollection;

/**
 * Form countries selector element used to create and
 * handle a select element to choose countries.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method Application_Countries_Selector setName($name)
 * @method Application_Countries_FilterCriteria getFilters()
 * @property Application_Countries $collection
 * @property Application_Countries_FilterCriteria $filters
 *
 */
class Application_Countries_Selector extends Application_Formable_RecordSelector
{
   /**
    * @var bool
    */
    protected bool $includeInvariant = true;
    private ?CountriesCollection $customCollection = null;

    public function excludeInvariant() : Application_Countries_Selector
    {
        $this->includeInvariant = false;
        
        return $this;
    }
    
    public function createCollection() : DBHelper_BaseCollection
    {
        return Application_Countries::getInstance();
    }
    
    protected function configureFilters() : void
    {
        if(!$this->includeInvariant)
        {
            $this->filters->excludeInvariant();
        }

        if(isset($this->customCollection))
        {
            $this->filters->selectCountryIDs($this->customCollection->getIDs());
        }
    }

    public function useCustomCollection(CountriesCollection $collection) : self
    {
        $this->customCollection = $collection;
        return $this;
    }
    
    protected function configureEntry(Application_Formable_RecordSelector_Entry $entry) : void
    {
        
    }
}
