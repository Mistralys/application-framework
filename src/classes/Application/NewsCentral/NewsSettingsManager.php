<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\NewsCentral\Categories\CategorySelector;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use AppLocalize\Localization;
use AppUtils\Microtime;
use AppUtils\Microtime_Exception;
use AppUtils\NamedClosure;
use Closure;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Element;
use HTML_QuickForm2_Element_HTMLDateTimePicker;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Select;
use HTML_QuickForm2_Element_Switch;
use HTML_QuickForm2_Element_Textarea;
use NewsCentral\Entries\Criticalities\NewsEntryCriticalities;
use NewsCentral\Entries\NewsEntry;
use UI;

/**
 * @property NewsEntry|NULL $record
 * @property NewsCollection $collection
 */
class NewsSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LOCALE = 'locale';
    public const string SETTING_CATEGORIES = 'categories';
    private bool $isAlert = false;
    private HTML_QuickForm2_Element_HTMLDateTimePicker $elToDate;
    private HTML_QuickForm2_Element_HTMLDateTimePicker $elFromDate;
    private HTML_QuickForm2_Element_HTMLDateTimePicker $elDateModified;
    private HTML_QuickForm2_Element_HTMLDateTimePicker $elDateCreated;

    public function __construct(Application_Interfaces_Formable $formable, NewsCollection $collection, ?NewsEntry $record = null)
    {
        parent::__construct($formable, $collection, $record);

        if(isset($this->record) && $this->record->isAlert()) {
            $this->makeAlert();
        }

        $this->setDefaultsUseStorageNames(true);
    }

    public function makeAlert() : self
    {
        $this->isAlert = true;
        return $this;
    }

    // region: Data handling

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {

    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        $now = Microtime::createNow()->getMySQLDate();

        $recordData->setKey(NewsCollection::COL_AUTHOR, $this->getUser()->getID());
        $recordData->setKey(NewsCollection::COL_DATE_CREATED, $now);
        $recordData->setKey(NewsCollection::COL_DATE_MODIFIED, $now);

        if(!$this->isAlert)
        {
            $recordData->setKey(NewsCollection::COL_NEWS_TYPE, NewsEntryTypes::NEWS_TYPE_ARTICLE);
            $recordData->setKey(NewsCollection::COL_REQUIRES_RECEIPT, 'no');
            $recordData->setKey(NewsCollection::COL_CRITICALITY, NewsEntryCriticalities::DEFAULT_CRITICALITY);
        } else
        {
            $recordData->setKey(NewsCollection::COL_NEWS_TYPE, NewsEntryTypes::NEWS_TYPE_ALERT);
        }
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        $this->record
            ->getCategoriesManager()
            ->setCategoryIDs(
                (array)$internalValues->getKey(self::SETTING_CATEGORIES)
            );
    }

    // endregion

    // region: Settings

    public const string SETTING_TITLE = 'label';
    public const string SETTING_SYNOPSIS = 'synopsis';
    public const string SETTING_ARTICLE = 'article';
    public const string SETTING_FROM_DATE = 'from_date';
    public const string SETTING_TO_DATE = 'to_date';
    public const string SETTING_CRITICALITY = 'criticality';
    public const string SETTING_REQUIRES_RECEIPT = 'receipt';
    public const string SETTING_DATE_CREATED = 'date_created';
    public const string SETTING_DATE_MODIFIED = 'date_modified';

    protected function registerSettings(): void
    {
        $this->registerMainSettings();
        $this->registerArticleText();
        $this->registerScheduling();
    }

    private function registerMainSettings() : void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_LOCALE)
            ->makeRequired()
            ->setStorageName(NewsCollection::COL_LOCALE)
            ->setCallback(Closure::fromCallable(array($this, 'injectLocale')));

        $group->registerSetting(self::SETTING_TITLE)
            ->makeRequired()
            ->setStorageName(NewsCollection::COL_LABEL)
            ->setCallback(NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'injectTitle')),
                array($this, 'injectTitle')
            ));

        $synopsis = $group->registerSetting(self::SETTING_SYNOPSIS)
            ->setStorageName(NewsCollection::COL_SYNOPSIS)
            ->setCallback(NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'injectSynopsis')),
                array($this, 'injectSynopsis')
            ));

        $group->registerSetting(self::SETTING_CATEGORIES)
            ->makeInternal()
            ->setCallback(Closure::fromCallable(array($this, 'injectCategories')));

        if($this->isEditMode())
        {
            $group->registerSetting(self::SETTING_DATE_CREATED)
                ->makeRequired()
                ->setCallback(Closure::fromCallable(array($this, 'injectDateCreated')));

            $group->registerSetting(self::SETTING_DATE_MODIFIED)
                ->makeRequired()
                ->setCallback(Closure::fromCallable(array($this, 'injectDateModified')));
        }

        if(!$this->isAlert)
        {
            return;
        }

        $synopsis->makeRequired();

        $group->registerSetting(self::SETTING_CRITICALITY)
            ->makeRequired()
            ->setDefaultValue(NewsEntryCriticalities::DEFAULT_CRITICALITY)
            ->setStorageName(NewsCollection::COL_CRITICALITY)
            ->setCallback(Closure::fromCallable(array($this, 'injectCriticality')));

        $group->registerSetting(self::SETTING_REQUIRES_RECEIPT)
            ->setDefaultValue('false')
            ->setImportFilter(static function($value) : string {
                return bool2string($value);
            })
            ->setStorageName(NewsCollection::COL_REQUIRES_RECEIPT)
            ->setStorageFilter(static function ($value) : string {
                return bool2string($value, true);
            })
            ->setCallback(Closure::fromCallable(array($this, 'injectRequiresReceipt')));
    }

    public function getDefaultValues(): array
    {
        $values = parent::getDefaultValues();

        if(isset($this->record)) {
            $values[self::SETTING_CATEGORIES] = $this->record->getCategoriesManager()->getCategoryIDs();
        }

        return $values;
    }

    private function registerArticleText() : void
    {
        if($this->isAlert)
        {

            return;
        }

        $group = $this->addGroup(t('Article'))
            ->setIcon(UI::icon()->text());

        $group->registerSetting(self::SETTING_ARTICLE)
            ->makeRequired()
            ->setStorageName(NewsCollection::COL_ARTICLE)
            ->setCallback(NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'injectArticle')),
                array($this, 'injectArticle')
            ));
    }

    private function registerScheduling() : void
    {
        $group = $this->addGroup(t('Scheduling'))
            ->setIcon(UI::icon()->calendar());

        $group->registerSetting(self::SETTING_FROM_DATE)
            ->setStorageFilter(Closure::fromCallable(array($this, 'filterDate')))
            ->setStorageName(NewsCollection::COL_SCHEDULED_FROM_DATE)
            ->setCallback(Closure::fromCallable(array($this, 'injectFromDate')));

        $group->registerSetting(self::SETTING_TO_DATE)
            ->setStorageFilter(Closure::fromCallable(array($this, 'filterDate')))
            ->setStorageName(NewsCollection::COL_SCHEDULED_TO_DATE)
            ->setCallback(Closure::fromCallable(array($this, 'injectToDate')));
    }

    private function injectCriticality() : HTML_QuickForm2_Element_Select
    {
        $el = $this->addElementSelect(self::SETTING_CRITICALITY, t('Criticality'));
        $el->setComment(sb()
            ->t('Selects the visual styling of the alert.')
        );

        $items = NewsEntryCriticalities::getInstance()->getAll();

        foreach ($items as $item)
        {
            $el->addOption($item->getLabel(), $item->getID());
        }

        return $el;
    }

    private function injectRequiresReceipt() : HTML_QuickForm2_Element_Switch
    {
        $el = $this->addElementSwitch(self::SETTING_REQUIRES_RECEIPT, t('Requires receipt?'));
        $el->setComment(sb()
            ->t('If enabled, dismissing the alert will require the user to confirm that they have read it.')
        );

        return $el;
    }

    private function injectDateCreated() : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->addElementDatepicker(self::SETTING_DATE_CREATED, t('Date created'));

        $this->elDateCreated = $el;

        return $el;
    }

    private function injectDateModified() : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->addElementDatepicker(self::SETTING_DATE_MODIFIED, t('Date modified'));

        $this->elDateModified = $el;

        $this->addRuleCallback(
            $el,
            function () : bool
            {
                $dateModified = $this->elDateModified->getDate();
                $dateCreated = $this->elDateCreated->getDate();

                if($dateModified === null || $dateCreated === null) {
                    return true;
                }

                return $dateModified >= $dateCreated;
            },
            t('The modified date cannot be before the created date.')
        );

        return $el;
    }

    private function injectFromDate() : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->addElementDatepicker(self::SETTING_FROM_DATE, t('From date'));
        $el->setComment(sb()
            ->t('If a date and time is selected, the article will become visible at that time - if it has been published.')
        );

        $this->elFromDate = $el;

        return $el;
    }

    private function injectToDate() : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->addElementDatepicker(self::SETTING_TO_DATE, t('To date'));
        $el->setComment(sb()
            ->t('If a date and time is selected, the article will automatically be hidden from that point onwards.')
        );

        $this->elToDate = $el;

        $this->addRuleCallback(
            $el,
            Closure::fromCallable(array($this, 'validateScheduleDates')),
            t('The "To date" must be after the "From date".')
        );

        return $el;
    }

    private function validateScheduleDates() : bool
    {
        $fromDate = $this->elFromDate->getValue();
        $toDate = $this->elToDate->getValue();

        if(empty($fromDate) || empty($toDate))
        {
            return true;
        }

        return $fromDate < $toDate;
    }

    /**
     * @param string|NULL $value
     * @return string|NULL
     * @throws Microtime_Exception
     */
    private function filterDate(?string $value) : ?string
    {
        if(empty($value)) {
            return null;
        }

        return Microtime::createFromString($value)->getMySQLDate();
    }

    private function injectArticle() : HTML_QuickForm2_Element_Textarea
    {
        $this->addElementHTML('<div class="markdown-editor">');

        $toolbarID = nextJSID();
        $this->addElementHTML('<div id="'.$toolbarID.'" class="markdown-toolbar"></div>');

        $el = $this->addElementTextarea(self::SETTING_ARTICLE, t('Article text'));
        $el->setRows(14);
        $el->addFilterTrim();
        $el->setStyle('width:96%');

        $this->ui->addJavascript('markup-editor/tiny-mde/tiny-mde.min.js');
        $this->ui->addStylesheet('markup-editor/tiny-mde/custom.css');
        $this->ui->addStylesheet('markup-editor/tiny-mde/tiny-mde.min.css');

        $jsName = 'MDE'.nextJSID();
        $this->ui->addJavascriptOnload(sprintf(
            "var %1\$s = new TinyMDE.Editor({element: '%2\$s'});",
            $jsName,
            $el->getId()
        ));

        $this->ui->addJavascriptOnload(sprintf(
            "new TinyMDE.CommandBar({element: '%1\$s', editor: %2\$s});",
            $toolbarID,
            $jsName
        ));

        $el->setComment(sb()
            ->t(
                'You may use %1$s syntax for formatting, links and more.',
                sb()->link('Markdown', 'https://commonmark.org/help/', true)
            )
            ->nl()
            ->t('In addition, the following specialized commands are available:')
            ->ul(array(
                sb()->code('{media: 42 width="300"}')->t('Embeds media with the given ID.'),
                sb()->code('{TOC}')->t('Inserts a table of contents.'),
            ))
        );

        $this->makeLengthLimited($el, 10, 150000);
        $this->makeStandalone($el);

        $this->addElementHTML('</div>');

        return $el;
    }

    private function injectCategories() : HTML_QuickForm2_Element
    {
        return new CategorySelector($this)
            ->setName(self::SETTING_CATEGORIES)
            ->setComment(sb()
                ->t('Use this to select in which categories this news entry should be included.')
                ->nl()
                ->hint()
                ->t('News can be filtered by category, so select all matching topics for the entry to be found.')
            )
            ->inject();
    }

    private function injectLocale() : HTML_QuickForm2_Element_Select
    {
        $el = $this->addElementSelect(self::SETTING_LOCALE, t('Locale'));
        $el->setComment(sb()
            ->t('Selects the language in which the article is written.')
        );

        $locales = Localization::getContentLocales();

        foreach ($locales as $locale)
        {
            $el->addOption($locale->getLabel(), $locale->getName());
        }

        return $el;
    }

    private function injectTitle() : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addElementText(self::SETTING_TITLE, t('Title'));
        $el->addFilterTrim();
        $el->addClass('input-xxlarge');

        $this->addRuleNameOrTitle($el);
        $this->makeLengthLimited($el, 1, 120);

        return $el;
    }

    private function injectSynopsis() : HTML_QuickForm2_Element_Textarea
    {
        $el = $this->addElementTextarea(self::SETTING_SYNOPSIS, t('Synopsis'));
        $el->addFilterTrim();

        $this->addRuleNoHTML($el);
        $this->makeLengthLimited($el, 0, 500);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_TITLE;
    }

    // endregion

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditNews();
    }
}
