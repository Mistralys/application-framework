<?php

declare(strict_types=1);

namespace Application\AppSets;

use Application\Admin\Welcome\Screens\WelcomeArea;
use Application\AppFactory;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Node;
use UI;
use UI\CSSClasses;

final class AppSetSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LABEL = 'label';
    public const string SETTING_ALIAS = 'alias';
    public const string SETTING_DEFAULT_AREA = 'defaultArea';
    public const string SETTING_ENABLED_AREAS = 'enabledAreas';

    public function __construct(Application_Interfaces_Formable $formable, ?AppSet $record)
    {
        parent::__construct($formable, AppSetsCollection::getInstance(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->expand()
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_LABEL)
            ->makeRequired()
            ->setStorageName(AppSetsCollection::COL_LABEL)
            ->setCallback($this->injectLabel(...));

        $group->registerSetting(self::SETTING_ALIAS)
            ->makeRequired()
            ->setStorageName(AppSetsCollection::COL_ALIAS)
            ->setCallback($this->injectAlias(...));

        $group = $this->addGroup(t('Administration areas'))
            ->expand()
            ->setIcon(UI::icon()->list());

        $group->registerSetting(self::SETTING_DEFAULT_AREA)
            ->makeRequired()
            ->setDefaultValue(WelcomeArea::URL_NAME)
            ->setStorageName(AppSetsCollection::COL_DEFAULT_URL_NAME)
            ->setCallback($this->injectDefaultArea(...));

        $group->registerSetting(self::SETTING_ENABLED_AREAS)
            ->setStorageName(AppSetsCollection::COL_URL_NAMES)
            ->setCallback($this->injectEnabledAreas(...))
            ->setImportFilter(function ($value) {
                if (is_string($value)) {
                    return explode(',', $value);
                }
                return array();
            })
            ->setStorageFilter(function ($value) {
                if (is_array($value)) {
                    return implode(',', $value);
                }
                return '';
            });

        $group = $this->addGroup(t('Documentation'))
            ->setIcon(UI::icon()->help());

        $group->registerSetting('description')
            ->setStorageName(AppSetsCollection::COL_DESCRIPTION)
            ->setCallback($this->injectDescription(...));
    }

    private function injectDescription(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementTextarea($setting->getName(), t('Description'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->setRows(5);
        $el->setComment(sb()
            ->t('Use this to document the use of this application set.')
            ->t('It will be shown in the %1$s tab.', t('Documentation'))
        );

        $this->addMarkdownSupport($el);

        return $el;
    }

    private function injectDefaultArea(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementSelect($setting->getName(), t('Default admin area'));
        $el->setComment(t('The UI will start with this administration area by default.'));
        $this->addRuleCallback(
            $el,
            $this->validateDefaultArea(...),
            t('Area must be enabled below.')
        );

        $areas = AppFactory::createDriver()->getAdminAreaObjects();

        usort($areas, static function (AdminAreaInterface $a, AdminAreaInterface $b) : int {
            return strnatcasecmp($a->getTitle(), $b->getTitle());
        });

        foreach ($areas as $area) {
            $el->addOption($area->getTitle(), $area->getURLName());
        }

        return $el;
    }

    private function validateDefaultArea() : bool
    {
        $driver = AppFactory::createDriver();
        $urlName = $this->requireElementByName(self::SETTING_DEFAULT_AREA)->getValue();
        $area = $driver->createArea($urlName);

        // Core areas are always enabled, so they are valid by default
        if ($area->isCore()) {
            return true;
        }

        $enabled = $this->requireElementByName(self::SETTING_ENABLED_AREAS)->getValue();
        $list = array();
        if(is_array($enabled)) {
            $list = $enabled;
        }

        // Check that the selected default area is in the list of enabled areas
        return in_array($area->getURLName(), $list, true);
    }

    private function injectEnabledAreas(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementExpandableSelect($setting->getName(), t('Optional areas'));
        $el->setComment(sb()
            ->t('Select any optional administration ara you wish to enable in this set.')
            ->nl()
            ->note()
            ->t('If the selected areas depend on other areas, these will automatically be enabled as needed.')
        );

        $areas = AppFactory::createDriver()->getAdminAreaObjects();

        usort($areas, static function (AdminAreaInterface $a, AdminAreaInterface $b) : int {
            return strnatcasecmp($a->getTitle(), $b->getTitle());
        });

        foreach ($areas as $area) {
            if ($area->isCore()) {
                continue;
            }

            $el->addOption($area->getTitle(), $area->getURLName());
        }

        return $el;
    }

    private function injectAlias(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Alias'));
        $el->addClass(CSSClasses::INPUT_XLARGE);
        $el->setComment(t('The alias is a system identifier used to reference the application set.'));

        $this->appendGenerateAliasButton($el, $this->requireElementByName(self::SETTING_LABEL));
        $this->addRuleAlias($el, true);
        $this->addRuleCallback($el, $this->validateAlias(...), t('An application set with this alias already exists.'));

        return $el;
    }

    /**
     * @param string $setID
     * @return boolean
     */
    private function validateAlias(string $setID): bool
    {
        $id = AppSetsCollection::getInstance()->getIDByAlias($setID);

        if($id === null) {
            return true;
        }

        if(isset($this->record)) {
            return $id === $this->record->getID();
        }

        return false;
    }

    private function injectLabel(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Label'));
        $el->addFilterTrim();
        $el->addClass(CSSClasses::INPUT_XXLARGE);

        $this->addRuleNameOrTitle($el);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_LABEL;
    }

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    public function isUserAllowedEditing(): bool
    {
        return true;
    }
}
