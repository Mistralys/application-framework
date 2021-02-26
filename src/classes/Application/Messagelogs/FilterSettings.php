<?php

/**
 * @property Application_Messagelogs $collection
 */
class Application_Messagelogs_FilterSettings extends DBHelper_BaseFilterSettings
{
    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::registerSettings()
     */
    protected function registerSettings()
    {
        $this->registerSetting('category', t('Category'), '');
        $this->registerSetting('type', t('Type'), '');
        $this->registerSetting('date', t('Date'));
        $this->registerSetting('search', t('Search'));
    }

    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::injectElements()
     */
    protected function injectElements(HTML_QuickForm2_Container $container)
    {
        $categories = $this->addSelect('category', $container);
        $categories->addOption(t('All categories'), '');
        
        $list = $this->collection->getAvailableCategories();
        foreach($list as $category) {
            $categories->addOption($category, $category);
        }
        
        $types = $this->addSelect('type', $container);
        $types->addOption(t('All types'), '');
        
        $list = $this->collection->getAvailableTypes();
        foreach($list as $type) {
            $types->addOption($type, $type);
        }
        
        $this->addElementDateSearch('date', $container);
        
        $fields = $this->collection->getRecordSearchableColumns();
        $this->addElementSearch(array_values($fields), $container);
    }
    
   /**
    * @var Application_Messagelogs_FilterCriteria
    */
    protected $filters;

    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::_configureFilters()
     */
    protected function _configureFilters()
    {
        $this->filters->selectDate($this->getSetting('date'));   
        
        $this->filters->selectCategory($this->getSetting('category'));
        
        $this->filters->selectType($this->getSetting('type'));
        
        $this->filters->setSearch(strval($this->getSetting('search')));
    }
}