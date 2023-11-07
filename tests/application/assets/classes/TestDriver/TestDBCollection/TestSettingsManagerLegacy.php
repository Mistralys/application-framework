<?php

declare(strict_types=1);

namespace TestDriver\TestDBCollection;

use Application_Exception;
use Application_Formable_RecordSettings;
use Application_Formable_RecordSettings_ValueSet;
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
    public const PREFIX_GENERATED_ALIAS = 'generated-';
    public const SETTING_GENERATE_ALIAS = 'generate-alias';

    /**
     * @return void
     * @throws Application_Exception
     * @see self::inject_alias()
     * @see self::inject_label()
     * @see self::inject_generate_alias()
     */
    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(TestDBRecord::COL_LABEL)
            ->makeRequired()
            ->makeDefault()
            ->setDefaultValue('Test label');

        $group->registerSetting(self::SETTING_GENERATE_ALIAS)
            ->makeInternal()
            ->makeRequired()
            ->setDefaultValue('test-alias');

        $group->registerSetting(TestDBRecord::COL_ALIAS)
            ->makeVirtual('')
            ->setStorageFilter(function($value, Application_Formable_RecordSettings_ValueSet $valueSet) : string
            {
                $valueSet->requireNotEmpty(self::SETTING_GENERATE_ALIAS);

                return self::PREFIX_GENERATED_ALIAS .$valueSet->getKey(self::SETTING_GENERATE_ALIAS);
            });
    }

    public function inject_generate_alias() : HTML_QuickForm2_Node
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
        $el = $this->addElementText(TestDBRecord::COL_LABEL, t('Label'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $this->addRuleLabel($el);
        $this->makeLengthLimited($el, 0, 180);
        $this->makeRequired($el);

        return $el;
    }

    public function inject_alias() : HTML_QuickForm2_Node
    {
        $el = $this->addElementText(TestDBRecord::COL_ALIAS, t('Alias'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $this->addRuleAlias($el);
        $this->makeLengthLimited($el, 0, 160);
        $this->makeRequired($el);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return TestDBRecord::COL_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return true;
    }
}
