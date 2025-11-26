<?php

declare(strict_types=1);

namespace Application\Renamer\Index;

use Application\FilterSettings\SettingDef;
use DBHelper_BaseFilterSettings;
use Application\Renamer\RenamingManager;
use UI\CSSClasses;

/**
 * @property RenamerFilterCriteria $filters
 */
class RenamerFilterSettings extends DBHelper_BaseFilterSettings
{
    public const string SETTING_COLUMN = 'column';

    protected function registerSettings(): void
    {
        $this->registerSetting(self::SETTING_COLUMN, t('DB Column'))
            ->setInjectCallback($this->inject_column(...))
            ->setConfigureCallback($this->configure_column(...));
    }

    private function inject_column(SettingDef $setting) : void
    {
        $el = $this->addElementSelect($setting->getName());
        $el->setLabel($setting->getLabel());
        $el->addClass(CSSClasses::INPUT_XXLARGE);

        $el->addOption(t('Any'), '');

        foreach(RenamingManager::getInstance()->getColumns()->getAll() as $column) {
            $el->addOption($column->getLabel(), $column->getID());
        }
    }

    private function configure_column() : void
    {
        $collection = RenamingManager::getInstance()->getColumns();

        $columnID = $this->getSetting(self::SETTING_COLUMN);
        if(!empty($columnID) && $collection->idExists($columnID)) {
            $this->filters->selectColumn($collection->getByID($columnID));
        }
    }

    protected function _configureFilters(): void
    {
    }
}
