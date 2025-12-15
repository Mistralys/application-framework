<?php

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use Application\AppFactory;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use Closure;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Element_InputText;
use UI;

class CategorySettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LABEL = 'label';

    public function __construct(Application_Interfaces_Formable $formable, ?Category $record = null)
    {
        parent::__construct($formable, AppFactory::createNews()->createCategories(), $record);
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
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_LABEL)
            ->makeRequired()
            ->setStorageName(CategoriesCollection::COL_LABEL)
            ->setCallback(Closure::fromCallable(array($this, 'injectLabel')));
    }

    private function injectLabel() : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addElementText(self::SETTING_LABEL, t('Label'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');

        $this->makeLengthLimited($el, 1, 160);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditNews();
    }
}
