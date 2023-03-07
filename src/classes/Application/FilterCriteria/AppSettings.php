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
    protected function getSelect() : string
    {
        return '*';
    }

    protected function getSearchFields() : array
    {
        return array('data_key', 'data_value');
    }

    protected function getQuery()
    {
        return 'SELECT {WHAT} FROM `app_settings` {JOINS} {WHERE} {GROUPBY} {ORDERBY} {LIMIT}';
    }
    
    public function addSetting(string $name, $value) : self
    {
        Application_Driver::createSettings()->set($name, $value);
        return $this;
    }
    
    public function getSetting(string $name, ?string $default=null) : ?string
    {
        return Application_Driver::createSettings()->get($name, $default);
    }

    public function getBoolSetting(string $name, bool $default=false) : bool
    {
        return Application_Driver::createSettings()->getBool($name, $default);
    }
    
    public function settingExists(string $name) : bool
    {
        return Application_Driver::createSettings()->exists($name);
    }
    
    public function deleteSetting(string $name) : self
    {
        Application_Driver::createSettings()->delete($name);

        DBHelper::deleteRecords(
            'app_settings', 
            array(
                'data_key' => $name
            )
        );

        return $this;
    }

    protected function _registerJoins() : void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void
    {
    }
}
