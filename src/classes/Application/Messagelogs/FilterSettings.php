<?php

/**
 * @property Application_Messagelogs $collection
 * @property Application_Messagelogs_FilterCriteria $filters
 */
class Application_Messagelogs_FilterSettings extends DBHelper_BaseFilterSettings
{
    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::registerSettings()
     */
    protected function registerSettings() : void
    {
        $this->registerSetting('category', t('Category'), '');
        $this->registerSetting('type', t('Type'), '');
        $this->registerSetting('date', t('Date'));
        $this->registerSetting('search', t('Search'));
    }

    protected function injectElements(HTML_QuickForm2_Container $container) : void
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
     * {@inheritDoc}
     * @see Application_FilterSettings::_configureFilters()
     */
    protected function _configureFilters() : void
    {
        $this->filters->selectDate((string)$this->getSetting('date'));
        
        $this->filters->selectCategory((string)$this->getSetting('category'));
        
        $this->filters->selectType((string)$this->getSetting('type'));
        
        $this->filters->setSearch((string)$this->getSetting('search'));
    }
}
