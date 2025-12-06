<?php

declare(strict_types=1);

namespace Application\Renamer;

use Application_Interfaces_Formable;
use UI;
use UI\CSSClasses;

class RenamerSettingsManager
{
    public const string SETTING_COLUMNS = 'columns';
    public const string SETTING_SEARCH = 'search';
    public const string SETTING_CASE_SENSITIVE = 'case_sensitive';

    private RenamingManager $manager;
    private Application_Interfaces_Formable $formable;

    public function __construct(RenamingManager $manager, Application_Interfaces_Formable $formable)
    {
        $this->manager = $manager;
        $this->formable = $formable;
    }

    public function inject() : void
    {
        $this->formable->createFormableForm('renamer-settings', array(self::SETTING_CASE_SENSITIVE => 'true'));

        $this->formable->addSection(t('Search Settings'))
            ->setIcon(UI::icon()->settings())
            ->expand();

        $this->injectSearch();
        $this->injectCaseSensitive();
        $this->injectColumns();

        $this->formable->setDefaultElement(self::SETTING_SEARCH);
    }

    private function injectCaseSensitive() : void
    {
        $el = $this->formable->addElementSwitch(self::SETTING_CASE_SENSITIVE, t('Case Sensitive?'));
        $el->makeYesNo();
        $el->setComment(sb()
            ->t('If enabled, only exact case matches of the search string will be found.')
        );
    }

    private function injectSearch() : void
    {
        $el = $this->formable->addElementText(self::SETTING_SEARCH, t('Search for'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->setComment(sb()
            ->t('The text to search for in the database.')
            ->t('The search is not case sensitive, and will find all occurrences of the specified text.')
        );

        $this->formable->makeRequired($el);
    }

    private function injectColumns() : void
    {
        $el = $this->formable->addElementExpandableSelect(self::SETTING_COLUMNS, t('Columns'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->makeMultiple();
        $el->setComment(sb()
            ->t('Selects the database columns in which to run the search and replace operation.')
        );

        foreach($this->manager->getColumns()->getAll() as $column) {
            $el->addOption(
                $column->getLabel(),
                $column->getID()
            );
        }

        $this->formable->makeRequired($el);
    }
}
