<?php

use AppUtils\ClassHelper;

/**
 * 
 * @property Application_RevisionableCollection_FilterCriteria $filters
 *
 */
abstract class Application_RevisionableCollection_FilterSettings extends Application_FilterSettings
{
    public const FILTER_STATE = 'state';
    /**
     * @var Application_RevisionableCollection
     */
    protected $collection;
    
    public function __construct(Application_RevisionableCollection $collection)
    {
        $this->collection = $collection;
        
        parent::__construct($collection->getRecordTypeName().'-list');
    }
    
   /**
    * @return Application_RevisionableCollection
    */
    public function getCollection()
    {
        return $this->collection;
    }
    
   /**
    * @var Application_RevisionableCollection_FilterSettings_StateFilter
    */
    protected $stateConfig;
    
   /**
    * Registers the revisionable's state to be filterable.
    * Use the return value of the method to configure the
    * states that can be filtered.
    * 
    * Afterwards, use the {@link inject_revisionable_state()} method
    * to inject the matching element into the form where you
    * want it.  The rest is automatic.
    * 
    * @return Application_RevisionableCollection_FilterSettings_StateFilter
    */
    protected function registerStateSetting($default=null) : Application_RevisionableCollection_FilterSettings_StateFilter
    {
        $this->stateConfig = new Application_RevisionableCollection_FilterSettings_StateFilter($this);
        
        $this->registerSetting(self::FILTER_STATE, t('State'), $default, Application_RevisionableCollection_FilterSettings_StateFilter::class);

        return $this->stateConfig;
    }
    
    protected function inject_search() : HTML_QuickForm2_Element_InputText
    {
        $searchFields = array_values($this->collection->getRecordSearchableColumns());

        return $this->addElementSearch($searchFields);
    }
    
}
