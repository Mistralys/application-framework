<?php
/**
 * File containing the {@link Application_FilterCriteria_AppSettings} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_FilterCriteria_AppSettings
 */

/**
 * Filters for accessing the custom application settings.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_FilterCriteria
 */
class Application_FilterCriteria_AppSettings extends Application_FilterCriteria_Database
{
    /**
     * {@inheritDoc}
     * @see Application_FilterCriteria::getSelect()
     */
    protected function getSelect()
    {
        return '*';
    }

    /**
     * {@inheritDoc}
     * @see Application_FilterCriteria::getSearchFields()
     */
    protected function getSearchFields()
    {
        return array('data_value');
    }

    /**
     * {@inheritDoc}
     * @see Application_FilterCriteria::getQuery()
     */
    protected function getQuery()
    {
        return 'SELECT {WHAT} FROM `app_settings` {JOINS} {WHERE} {GROUPBY} {ORDERBY} {LIMIT}';
    }
    
    public function addSetting($name, $value)
    {
        Application_Driver::setSetting($name, $value);
        return $this;
    }
    
    public function getSetting($name, $default=null)
    {
        return Application_Driver::getSetting($name, $default);
    }

    public function getBoolSetting(string $name, bool $default=false) : bool
    {
        return Application_Driver::getBoolSetting($name, $default);
    }
    
    public function settingExists($name)
    {
        $value = $this->getSetting($name);
        return $value !== "" && $value !== null;
    }
    
    public function deleteSetting($name)
    {
        Application_Driver::deleteSetting($name);

        DBHelper::deleteRecords(
            'app_settings', 
            array(
                'data_key' => $name
            )
        );
    }
}
