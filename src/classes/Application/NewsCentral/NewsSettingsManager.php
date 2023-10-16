<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use AppUtils\Microtime;
use AppUtils\NamedClosure;
use Closure;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Element_HTMLDateTimePicker;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Select;
use HTML_QuickForm2_Element_Switch;
use HTML_QuickForm2_Element_Textarea;
use UI;

/**
 * @property NewsEntry|NULL $record
 * @property NewsCollection $collection
 */
class NewsSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const SETTING_TITLE = 'label';
    public const SETTING_SYNOPSIS = 'synopsis';
    public const SETTING_ARTICLE = 'article';
    public const SETTING_FROM_DATE = 'from_date';
    public const SETTING_TO_DATE = 'to_date';
    public const SETTING_CRITICALITY = 'criticality';
    public const SETTING_REQUIRES_RECEIPT = 'requires_receipt';

    private bool $isAlert = false;

    public function __construct(Application_Formable $formable, NewsCollection $collection, ?NewsEntry $record = null)
    {
        parent::__construct($formable, $collection, $record);
    }

    public function makeAlert() : self
    {
        $this->isAlert = true;
        return $this;
    }

    protected function processPostCreateSettings(DBHelper_BaseRecord $record, array $formValues): void
    {

    }

    protected function getCreateData(array $formValues): array
    {
         $now = Microtime::createNow()->getMySQLDate();

        $formValues[NewsCollection::COL_AUTHOR] = $this->getUser()->getID();
        $formValues[NewsCollection::COL_DATE_CREATED] = $now;
        $formValues[NewsCollection::COL_DATE_MODIFIED] = $now;

        if(!$this->isAlert) {
            $formValues[NewsCollection::COL_REQUIRES_RECEIPT] = 'no';
            $formValues[NewsCollection::COL_NEWS_TYPE] = NewsCollection::NEWS_TYPE_ARTICLE;
        } else {
            $formValues[NewsCollection::COL_NEWS_TYPE] = NewsCollection::NEWS_TYPE_ALERT;
        }

        $formValues[NewsCollection::COL_REQUIRES_RECEIPT] = bool2string($formValues[NewsCollection::COL_REQUIRES_RECEIPT], true);

        return $formValues;
    }

    protected function updateRecord(array $values): void
    {
        // TODO: Implement updateRecord() method.
    }

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

        $group->registerSetting(self::SETTING_TITLE)
            ->makeRequired()
            ->setCallback(NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'injectTitle')),
                array($this, 'injectTitle')
            ));

        $synopsis = $group->registerSetting(self::SETTING_SYNOPSIS)
            ->setCallback(NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'injectSynopsis')),
                array($this, 'injectSynopsis')
            ));

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
            ->setDefaultValue('no')
            ->setStorageName(NewsCollection::COL_REQUIRES_RECEIPT)
            ->setCallback(Closure::fromCallable(array($this, 'injectRequiresReceipt')));
    }

    private function registerArticleText() : void
    {
        if($this->isAlert) {
            return;
        }

        $group = $this->addGroup(t('Article'))
            ->setIcon(UI::icon()->text());

        $group->registerSetting(self::SETTING_ARTICLE)
            ->makeRequired()
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

    private function injectFromDate() : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->addElementDatepicker(self::SETTING_FROM_DATE, t('From date'));
        $el->setComment(sb()
            ->t('If a date and time is selected, the article will become visible at that time - if it has been published.')
        );

        return $el;
    }

    private function injectToDate() : HTML_QuickForm2_Element_HTMLDateTimePicker
    {
        $el = $this->addElementDatepicker(self::SETTING_TO_DATE, t('To date'));
        $el->setComment(sb()
            ->t('If a date and time is selected, the article will automatically be hidden from that point onwards.')
        );

        return $el;
    }

    /**
     * @param string|NULL $value
     * @return string|NULL
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
        $el = $this->addElementTextarea(self::SETTING_ARTICLE, t('Article text'));
        $el->setRows(14);
        $el->addFilterTrim();
        $el->setStyle('width:96%');

        $el->setComment(sb()
            ->t(
                'You may use %1$s syntax for formatting, links and more.',
                sb()->link('Markdown', 'https://commonmark.org/help/', true)
            )
        );

        $this->makeLengthLimited($el, 10, 150000);
        $this->makeStandalone($el);

        return $el;
    }

    private function injectTitle() : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addElementText(self::SETTING_TITLE, t('Title'));
        $el->addFilterTrim();

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

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditNews();
    }
}
