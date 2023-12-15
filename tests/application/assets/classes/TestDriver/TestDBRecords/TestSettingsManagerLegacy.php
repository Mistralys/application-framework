<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application_Exception;
use Application_Formable_RecordSettings;
use Application_Formable_RecordSettings_ValueSet;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Node;
use UI;

/**
 * Legacy settings manager: Does not use the form element
 * injection callbacks.
 *
 * This has several issues:
 *
 * 1. The injection methods must be public
 * 2. The injection method names must match the setting name exactly
 * 3. The injection methods appear unused in static code analysis
 */
class TestSettingsManagerLegacy extends Application_Formable_RecordSettings
{
    public const SETTING_GENERATE_ALIAS = 'local-generate-alias';
    public const SETTING_STATIC = 'local-static';
    public const VALUE_PREFIX_ALIAS = 'generated-';
    public const VALUE_STATIC = 'static-value';

    /**
     * @return void
     * @throws Application_Exception
     * @see self::inject_alias()
     * @see self::inject_label()
     * @see self::inject_local_generate_alias()
     * @see self::inject_local_static()
     */
    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(TestDBCollection::COL_LABEL)
            ->makeRequired()
            ->makeDefault()
            ->setDefaultValue('Test label');

        $group->registerSetting(self::SETTING_GENERATE_ALIAS)
            ->makeInternal()
            ->makeRequired()
            ->setDefaultValue('test-alias');

        $group->registerSetting(TestDBCollection::COL_ALIAS)
            ->makeVirtual('')
            ->setStorageFilter(function($value, Application_Formable_RecordSettings_ValueSet $valueSet) : string
            {
                $valueSet->requireNotEmpty(self::SETTING_GENERATE_ALIAS);

                return self::VALUE_PREFIX_ALIAS .$valueSet->getKey(self::SETTING_GENERATE_ALIAS);
            });

        $group->registerSetting(self::SETTING_STATIC)
            ->makeStatic()
            ->setDefaultValue(self::VALUE_STATIC);
    }

    public function inject_local_static() : HTML_QuickForm2_Node
    {
        $el = $this->addElementStatic('Static','Static value.');
        $el->setComment(sb()
            ->add('This is used only for display purposes, and no value is submitted in the form.')
        );

        return $el;
    }

    public function inject_local_generate_alias() : HTML_QuickForm2_Node
    {
        $el = $this->addElementText(self::SETTING_GENERATE_ALIAS, t('Internal field'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $el->setComment(t('Used to fill the virtual alias field.'));

        $this->addRuleAlias($el);
        $this->makeLengthLimited($el, 0, 160);
        $this->makeRequired($el);

        return $el;
    }

    public function inject_label() : HTML_QuickForm2_Node
    {
        $el = $this->addElementText(TestDBCollection::COL_LABEL, t('Label'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $this->addRuleLabel($el);
        $this->makeLengthLimited($el, 0, 180);
        $this->makeRequired($el);

        return $el;
    }

    public function inject_alias() : HTML_QuickForm2_Node
    {
        $el = $this->addElementText(TestDBCollection::COL_ALIAS, t('Alias'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $this->addRuleAlias($el);
        $this->makeLengthLimited($el, 0, 160);
        $this->makeRequired($el);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return TestDBCollection::COL_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return true;
    }

    protected function _afterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        self::verifyDataSet($data);
    }

    public static function verifyDataSet(Application_Formable_RecordSettings_ValueSet $data) : void
    {
        $data->requireNotEmpty(self::SETTING_GENERATE_ALIAS);
        $data->requireSame(TestDBCollection::COL_ALIAS, self::VALUE_PREFIX_ALIAS .$data->getKey(self::SETTING_GENERATE_ALIAS));
        $data->requireSame(self::SETTING_STATIC, self::VALUE_STATIC);
    }
}
