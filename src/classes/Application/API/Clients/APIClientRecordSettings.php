<?php

declare(strict_types=1);

namespace Application\API\Clients;

use Application\AppFactory;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Node;
use UI;
use UI\CSSClasses;

class APIClientRecordSettings extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LABEL = 'label';
    const string SETTING_FOREIGN_ID = 'foreign_id';

    public function __construct(Application_Interfaces_Formable $formable, ?APIClientRecord $record = null)
    {
        parent::__construct($formable, AppFactory::createAPIClients(), $record);

        $this->setDefaultsUseStorageNames(true);
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
        $settings = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $settings->registerSetting(self::SETTING_LABEL)
            ->setStorageName(APIClientsCollection::COL_LABEL)
            ->setCallback($this->injectLabel(...));

        $settings->registerSetting(self::SETTING_FOREIGN_ID)
            ->setStorageName(APIClientsCollection::COL_FOREIGN_ID)
            ->setCallback($this->injectForeignID(...));

        $settings->registerSetting('comments')
            ->setStorageName(APIClientsCollection::COL_COMMENTS)
            ->setCallback($this->injectComments(...));
    }

    private function injectComments(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementTextarea($key->getName(), t('Comments'));
        $el->setRows(3);
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->setComment(sb()
            ->t('Optional comments to document what the client is used for.')
            ->nl()
        );

        $this->addMarkdownSupport($el);
        $this->addRuleNoHTML($el);

        return $el;
    }

    private function injectForeignID(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($key->getName(), t('Foreign ID'));
        $el->addClass(CSSClasses::INPUT_XLARGE);

        $this->addRuleAlias($el, true);
        $this->makeLengthLimited($el, 1, 180);

        return $el;
    }

    private function injectLabel(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($key->getName(), t('Label'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);

        $this->addRuleNameOrTitle($el);
        $this->makeLengthLimited($el, 1, 180);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return true;
    }
}
