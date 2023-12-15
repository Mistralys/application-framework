<?php

use AppUtils\ClassHelper;

/**
 * 
 * @property Application_RevisionableCollection_FilterCriteria $filters
 *
 */
abstract class Application_RevisionableCollection_FilterSettings extends Application_FilterSettings
{
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
    * Afterwards, use the {@link injectStateElement()} method 
    * to inject the matching element into the form where you
    * want it.  The rest is automatic.
    * 
    * @return Application_RevisionableCollection_FilterSettings_StateFilter
    */
    protected function registerStateSetting($default=null)
    {
        $this->stateConfig = new Application_RevisionableCollection_FilterSettings_StateFilter($this);
        
        $this->registerSetting('state', t('State'), $default);
        $this->addAutoConfigure('state');
        
        return $this->stateConfig;
    }
    
   /**
    * Registers the search setting to add the generic search functionality.
    * 
    * Afterwards, use the {@link injectSearchElement()} method to inject
    * the matching element into the form where you want it. The rest is
    * automatic.
    */
    protected function registerSearchSetting()
    {
        $this->registerSetting('search', t('Text search'), '');
        $this->addAutoConfigure('search');
    }
    
    protected function injectSearchElement(HTML_QuickForm2_Container $container)
    {
        $this->requireSetting('search');
        
        $searchFields = array_values($this->collection->getRecordSearchableColumns());
        return $this->addElementSearch($searchFields, $container);
    }
    
    protected function injectStateElement(HTML_QuickForm2_Container $container)
    {
        $this->requireSetting('state');
        
        return $this->stateConfig->injectElement($container);
    }
    
    protected function autoConfigure_search()
    {
        if(!$this->hasSetting('search')) {
            return;
        }

        $search = trim($this->getSetting('search'));
        if (strlen($search) >= 2) {
            $this->filters->setSearch($search);
        }
    }
    
    protected function autoConfigure_state()
    {
        if(!$this->hasSetting('state')) {
            return;
        }
     
        $this->stateConfig->configure(
            ClassHelper::requireObjectInstanceOf(
                Application_RevisionableCollection_FilterCriteria::class,
                $this->filters
            )
        );
    }
}
