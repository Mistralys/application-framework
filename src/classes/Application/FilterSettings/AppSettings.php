<?php

class Application_FilterSettings_AppSettings extends Application_FilterSettings
{
    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::registerSettings()
     */
    protected function registerSettings()
    {
        $this->registerSetting('search', t('Search'));
    }

    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::injectElements()
     */
    protected function injectElements(HTML_QuickForm2_Container $container)
    {
        $this->addElementSearch(array('data_key', 'data_value'), $container);
    }

    /**
     * {@inheritDoc}
     * @see Application_FilterSettings::_configureFilters()
     */
    protected function _configureFilters()
    {
        $search = $this->getSetting('search');
        if(!empty($search)) 
        {
            $this->filters->setSearch(trim($search));
        }
    }
}