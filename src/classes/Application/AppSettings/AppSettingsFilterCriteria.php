<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\AppSettings;

use Application_Driver;
use Application_FilterCriteria_DatabaseExtended;
use DBHelper;
use DBHelper_StatementBuilder_ValuesContainer;

/**
 * Filters for accessing the custom application settings.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_FilterCriteria
 */
class AppSettingsFilterCriteria extends Application_FilterCriteria_DatabaseExtended
{
    const string TABLE_NAME = 'app_settings';
    const string COL_DATA_KEY = 'data_key';
    const string COL_DATA_VALUE = 'data_value';
    const string COL_ROLE = 'role';
    const string COL_EXPIRY_DATE = 'expiry_date';

    protected function getSelect(): string|array
    {
        return '*';
    }

    public function getSearchFields(): array
    {
        return array(
            self::COL_DATA_KEY,
            self::COL_DATA_VALUE
        );
    }

    public function getPrimaryKeyName(): string
    {
        return self::COL_DATA_KEY;
    }

    protected function getQuery() : string
    {
        return sprintf(
            /** @lang text */
            'SELECT {WHAT} FROM `%1$s` {JOINS} {WHERE} {GROUPBY} {ORDERBY} {LIMIT}',
            self::TABLE_NAME
        );
    }

    public function addSetting(string $name, $value): self
    {
        Application_Driver::createSettings()->set($name, $value);
        return $this;
    }

    public function getSetting(string $name, ?string $default = null): ?string
    {
        return Application_Driver::createSettings()->get($name, $default);
    }

    public function settingExists(string $name): bool
    {
        return Application_Driver::createSettings()->exists($name);
    }

    public function deleteSetting(string $name): self
    {
        Application_Driver::createSettings()->delete($name);

        DBHelper::deleteRecords(
            self::TABLE_NAME,
            array(
                self::COL_DATA_KEY => $name
            )
        );

        return $this;
    }

    protected function _registerJoins(): void
    {
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container): void
    {
    }

    protected function _initCustomColumns(): void
    {
    }

    /**
     * @return AppSettingRecord[]
     */
    public function getItemsObjects(): array
    {
        $result = array();
        foreach($this->getItems() as $item) {
            $result[] = new AppSettingRecord($item);
        }

        return $result;
    }
}
