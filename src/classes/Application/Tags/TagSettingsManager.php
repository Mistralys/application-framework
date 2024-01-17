<?php

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Closure;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Select;
use UI;

/**
 * @property TagRecord|NULL $record
 */
class TagSettingsManager extends Application_Formable_RecordSettings_Extended
{
    const SETTING_LABEL = 'label';
    public const SETTING_PARENT = 'parent';

    public function __construct(Application_Formable $formable, TagCollection $collection, ?TagRecord $record = null)
    {
        parent::__construct($formable, $collection, $record);

        $this->setDefaultsUseStorageNames(true);
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

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_LABEL)
            ->setStorageName(TagCollection::COL_LABEL)
            ->makeRequired()
            ->setCallback(Closure::fromCallable(array($this, 'injectLabel')));

        $group->registerSetting(self::SETTING_PARENT)
            ->setStorageName(TagCollection::COL_PARENT_TAG_ID)
            ->setStorageFilter(function ($value) :?int {
                if(!empty($value)) {
                    return (int)$value;
                }
                return null;
            })
            ->setCallback(Closure::fromCallable(array($this, 'injectParentTag')));
    }

    public function getTagID() : ?int
    {
        if(isset($this->record)) {
            return $this->record->getID();
        }

        return null;
    }

    private function injectParentTag() : HTML_QuickForm2_Element_Select
    {
        $el = $this->addElementMultiselect(self::SETTING_PARENT, t('Parent tag'));
        $el->enableFiltering();
        $el->setComment(sb()
            ->t('If no parent tag is selected, a new root tag will be created.')
            ->t('Changing the parent tag will move this tag to the new parent tag.')
            ->nl()
            ->noteBold()
            ->t('Avoid moving root tags, this can have big repercussions.')
        );

        $tags = AppFactory::createTags()
            ->getFilterCriteria()
            ->getItemsObjects();

        $el->addOption(t('Select a tag...'), '');

        $currentID = $this->getTagID();

        foreach($tags as $tag)
        {
            $tagID = $tag->getID();
            if($tagID === $currentID) {
                continue;
            }

            $el->addOption($tag->getLabel(), $tagID);
        }

        return $el;
    }

    private function injectLabel() : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addElementText(self::SETTING_LABEL, t('Label'));
        $el->addClass('input-xxlarge');
        $el->addFilterTrim();

        $this->addRuleLabel($el);
        $this->makeLengthLimited($el, 0, 160);

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
