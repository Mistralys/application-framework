<?php

declare(strict_types=1);

namespace Application\Countries;

use Application\AppFactory;
use Application_Countries_Country;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use Closure;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Node;
use UI\CSSClasses;

class CountrySettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LABEL = 'label';

    public function __construct(Application_Interfaces_Formable $formable, ?Application_Countries_Country $record = null)
    {
        parent::__construct($formable, AppFactory::createCountries(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditCountries();
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

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'));

        $group->registerSetting(self::SETTING_LABEL)
            ->makeRequired()
            ->setStorageName(Application_Countries_Country::COL_LABEL)
            ->setCallback(Closure::fromCallable(array($this, 'injectLabel')));
    }

    private function injectLabel(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Invariant label'));
        $el->addFilterTrim();
        $el->addClass(CSSClasses::INPUT_XLARGE);
        $el->setComment(sb()
            ->t('The label of the country, in invariant language (English).')
            ->t('It will be translated automatically to the selected user interface language.')
        );

        $this->makeLengthLimited($el, 0, 80);
        $this->addRuleLabel($el);

        return $el;
    }
}
