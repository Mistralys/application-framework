<?php

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Closure;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Select;
use UI;

/**
 * @property TagRecord|NULL $record
 */
class TagSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LABEL = 'label';
    public const string SETTING_PARENT = 'parent';
    public const string SETTING_SORT_TYPE = 'sort_type';

    private ?TagRecord $parentTag = null;

    public function __construct(Application_Formable $formable, TagCollection $collection, ?TagRecord $record = null)
    {
        parent::__construct($formable, $collection, $record);

        $this->setDefaultsUseStorageNames(true);
    }

    /**
     * Forces the use of a specific parent tag.
     * @param TagRecord|null $parentTag
     * @return $this
     */
    public function setParentTag(?TagRecord $parentTag) : self
    {
        $this->parentTag = $parentTag;
        return $this;
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
            ->setIcon(UI::icon()->settings())
            ->expand();

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

        $group = $this->addGroup(t('Sorting'))
            ->setIcon(UI::icon()->sort());

        $group->registerSetting(self::SETTING_SORT_TYPE)
            ->makeRequired()
            ->setStorageName(TagCollection::COL_SORT_TYPE)
            ->setDefaultValue($this->resolveDefaultSortType())
            ->setCallback(Closure::fromCallable(array($this, 'injectSortType')));
    }

    private function resolveDefaultSortType() : string
    {
        if($this->isRoot()) {
            return TagSortTypes::getInstance()->getDefaultForRoot()->getID();
        }

        return TagSortTypes::getInstance()->getDefaultID();
    }

    public function getTagID() : ?int
    {
        if(isset($this->record)) {
            return $this->record->getID();
        }

        return null;
    }

    public function isRoot() : bool
    {
        return isset($this->parentTag) || (isset($this->record) && $this->record->isRootTag());
    }

    private function injectSortType() : HTML_QuickForm2_Element_Select
    {
        $el = $this->addElementSelect(self::SETTING_SORT_TYPE, t('Sort type'));
        $el->setComment(sb()
            ->t('Determines how the tag\'s subtags are sorted (if it has any).')
            ->t('If you choose to sort by weight, the tags will be sortable manually in the tag tree.')
        );

        if($this->isRoot()) {
            $sortTypes = TagSortTypes::getInstance()->getForRoot();
        } else {
            $sortTypes = TagSortTypes::getInstance()->getAll();
        }

        foreach($sortTypes as $sortType) {
            $el->addOption($sortType->getLabel(), $sortType->getID());
        }

        return $el;
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

        if(isset($this->parentTag))
        {
            $el->addOption($this->parentTag->getLabel(), $this->parentTag->getID());
        }
        else
        {
            $tags = AppFactory::createTags()
                ->getFilterCriteria()
                ->getItemsObjects();

            $el->addOption(t('Select a tag...'), '');

            $currentID = $this->getTagID();

            foreach ($tags as $tag) {
                $tagID = $tag->getID();
                if ($tagID === $currentID) {
                    continue;
                }

                $el->addOption($tag->getLabel(), $tagID);
            }
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
