<?php

declare(strict_types=1);

namespace TestDriver\TestDBRecords;

use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use Closure;
use DBHelper\BaseRecordSettings;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Node;
use TestDriver\ClassFactory;
use UI;

/**
 * The form field values are different from the database
 * column names. The method {@see \Application_Formable_RecordSettings_Setting::setStorageName()}
 * guarantees that the values are passed with the correct
 * column names.
 */
class TestDBRecordSettingsManager extends BaseRecordSettings
{
    public const string SETTING_LABEL = 'local-label';
    public const string SETTING_ALIAS = 'local-alias';
    public const string SETTING_GENERATE_ALIAS = 'local-generate-alias';
    public const string SETTING_STATIC = 'local-static';

    public const string VALUE_PREFIX_ALIAS = 'generate-';
    public const string VALUE_STATIC = 'static-value';

    public function __construct(Application_Interfaces_Formable $formable, ?TestDBRecord $record = null)
    {
        parent::__construct($formable, ClassFactory::createTestDBCollection(), $record);
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        // Simple text field whose value is passed on
        // directly to the collection.
        $group->registerSetting(self::SETTING_LABEL)
            ->makeRequired()
            ->setStorageName(TestDBCollection::COL_LABEL)
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
            ->setStorageName(TestDBCollection::COL_ALIAS)
            ->setStorageFilter(function ($value, Application_Formable_RecordSettings_ValueSet $valueSet)
            {
                $valueSet->requireNotEmpty(self::SETTING_GENERATE_ALIAS);
                return self::VALUE_PREFIX_ALIAS .$valueSet->getKey(self::SETTING_GENERATE_ALIAS);
            });

        $group->registerSetting(self::SETTING_STATIC)
            ->makeStatic()
            ->setDefaultValue(self::VALUE_STATIC)
            ->setCallback(Closure::fromCallable(array($this, 'injectStatic')));
    }

    private function injectStatic() : HTML_QuickForm2_Node
    {
        $el = $this->addElementStatic('Static','Static value.');
        $el->setComment(sb()
            ->add('This is used only for display purposes, and no value is submitted in the form.')
        );

        return $el;
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

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function _afterSave(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        self::verifyValueSet($data);
    }

    public static function verifyValueSet(Application_Formable_RecordSettings_ValueSet $data) : void
    {
        $data->requireNotEmpty(self::SETTING_GENERATE_ALIAS);
        $data->requireNotEmpty(TestDBCollection::COL_LABEL);
        $data->requireSame(self::SETTING_STATIC, self::VALUE_STATIC);
        $data->requireSame(TestDBCollection::COL_ALIAS, self::VALUE_PREFIX_ALIAS.$data->getKey(self::SETTING_GENERATE_ALIAS));
    }
}
