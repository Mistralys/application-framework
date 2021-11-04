<?php

class Application_FilterSettings_AppSettings extends Application_FilterSettings
{
    protected function registerSettings() : void
    {
        $this->registerSetting('search', t('Search'));
    }

    protected function injectElements(HTML_QuickForm2_Container $container) : void
    {
        $this->addElementSearch(array('data_key', 'data_value'), $container);
    }

    protected function _configureFilters() : void
    {
        $search = $this->getSetting('search');
        if(!empty($search)) 
        {
            $this->filters->setSearch(trim($search));
        }
    }
}
