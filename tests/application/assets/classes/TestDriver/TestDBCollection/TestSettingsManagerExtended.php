<?php

declare(strict_types=1);

namespace TestDriver\TestDBCollection;

use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Closure;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Node;
use TestDriver\ClassFactory;
use UI;

class TestSettingsManagerExtended extends Application_Formable_RecordSettings_Extended
{
    public const SETTING_LABEL = 'custom-label';
    public const SETTING_ALIAS = 'custom-alias';
    public const SETTING_GENERATE_ALIAS = 'generate-alias';
    public const PREFIX_GENERATED_ALIAS = 'generate-';

    public function __construct(Application_Formable $formable, ?TestDBRecord $record = null)
    {
        parent::__construct($formable, ClassFactory::createTestDBCollection(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_LABEL)
            ->makeRequired()
            ->setStorageName(TestDBRecord::COL_LABEL)
            ->setDefaultValue('Test label')
            ->setCallback(Closure::fromCallable(array($this, 'injectLabel')));

        $group->registerSetting(self::SETTING_GENERATE_ALIAS)
            ->makeInternal()
            ->makeRequired()
            ->setDefaultValue('test-alias')
            ->setCallback(Closure::fromCallable(array($this, 'injectGenerateAlias')));

        $group->registerSetting(self::SETTING_ALIAS)
            ->makeRequired()
            ->makeVirtual('')
            ->setStorageName(TestDBRecord::COL_ALIAS)
            ->setStorageFilter(function ($value, Application_Formable_RecordSettings_ValueSet $valueSet)
            {
                $valueSet->requireNotEmpty(self::SETTING_GENERATE_ALIAS);
                return self::PREFIX_GENERATED_ALIAS .$valueSet->getKey(self::SETTING_GENERATE_ALIAS);
            });
    }

    private function injectLabel() : HTML_QuickForm2_Node
    {
        $el = $this->addElementText(self::SETTING_LABEL, t('Label'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');

        $this->addRuleLabel($el);
        $this->makeLengthLimited($el, 0, 180);

        return $el;
    }

    private function injectGenerateAlias() : HTML_QuickForm2_Node
    {
        $el = $this->addElementText(self::SETTING_GENERATE_ALIAS, t('Alias generator'));
        $el->setComment(sb()
            ->add('The value of this field is used to generate the value for the record\'s alias.')
            ->add('The alias is a virtual field with a storage filter callback.')
        );
        $el->addFilterTrim();
        $el->addClass('input-xlarge');

        $this->addRuleAlias($el);
        $this->makeLengthLimited($el, 0, 160);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->isDeveloper();
    }

    protected function processPostCreateSettings(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }
}
