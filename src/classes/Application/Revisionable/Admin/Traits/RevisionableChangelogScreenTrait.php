<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\Admin\Traits;

use Application_Changelog_Entry;
use Application_Changelog_FilterCriteria;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Microtime;
use UI;
use UI_DataGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @package Application
 * @subpackage Revisionables
 *
 * @see RevisionableChangelogScreenInterface
 */
trait RevisionableChangelogScreenTrait
{
    public function getTitle(): string
    {
        return t('Changelog');
    }

    public function getNavigationTitle(): string
    {
        return t('Changelog');
    }

    protected function _handleActions(): bool
    {
        $this->getRevisionableOrRedirect();

        if($this->request->getBool(RevisionableChangelogScreenInterface::REQUEST_PARAM_RESET)) {
            $this->handle_resetFilters();
            return true;
        }

        $this->createDataGrid();
        $this->createFilterForm();

        if ($this->isFormValid()) {
            $this->handle_filtersSubmitted();
        }

        return true;
    }

    protected function handle_resetFilters() : void
    {
        $this->user->setSetting($this->getListID(),'');
        $this->user->saveSettings();

        $this->redirectWithSuccessMessage(
            t('The filters have been reset successfully at %1$s.', sb()->time()),
            $this->getURL()
        );
    }

    protected function _handleSidebar(): void
    {
        $section = $this->sidebar->addSection();
        $section->setTitle(t('Filter the list'));
        $section->appendContent($this->renderFormable());
    }

    // region: Filter form

    protected function createFilterForm(): void
    {
        $changelog = $this->requireRevisionable()->getChangelog();

        $this->createFormableForm('changelog-filters', $this->getFiltersConfig());

        $this->injectAuthor();
        $this->injectType();
        $this->injectSearch();
        $this->injectRevision();
        $this->injectFromDate();
        $this->injectToDate();
        $this->injectButtons();

        $this->getFormInstance()->makeCondensed();
        $this->addHiddenVars($this->getPageParams());
        $this->addHiddenVars($changelog->getPrimary());
        $this->addHiddenVars($this->getPersistVars());
    }

    private function injectFromDate() : void
    {
        $el = $this->addElementDatepicker(RevisionableChangelogScreenInterface::FILTER_FROM_DATE, t('From date'));
        $el->setComment(sb()
            ->t('Limit to entries starting at (and including) this date and time.')
        );
    }

    private function injectToDate() : void
    {
        $el = $this->addElementDatepicker(RevisionableChangelogScreenInterface::FILTER_TO_DATE, t('To date'));
        $el->setTimeOptional();
        $el->setComment(sb()
            ->t('Limit to entries up to (and including) this date and time.')
        );

        $this->addRuleCallback(
            $el,
            $this->validateScheduleDates(...),
            t('The "To date" must be after the "From date".')
        );
    }

    private function validateScheduleDates() : bool
    {
        $fromDate = $this->requireElementByName(RevisionableChangelogScreenInterface::FILTER_FROM_DATE)->getValue();
        $toDate = $this->requireElementByName(RevisionableChangelogScreenInterface::FILTER_TO_DATE)->getValue();

        if(empty($fromDate) || empty($toDate)) {
            return true;
        }

        return $fromDate < $toDate;
    }

    private function injectRevision() : void
    {
        $el = $this->addElementText(RevisionableChangelogScreenInterface::FILTER_REVISION, t('Revision'))
            ->addFilterTrim();

        $this->addRuleInteger($el);
    }

    private function injectButtons() : void
    {
        $this->getFormInstance()->addButton('filter')
            ->setIcon(UI::icon()->filter())
            ->setLabel(t('Filter the list'))
            ->makeSubmit();

        $this->getFormInstance()->addButton('reset')
            ->setIcon(UI::icon()->reset())
            ->setLabel(t('Reset'))
            ->link($this->getResetURL());
    }

    private function injectAuthor() : void
    {
        $el = $this->addElementSelect(RevisionableChangelogScreenInterface::FILTER_AUTHOR, t('Author'));

        $el->addOption(t('All'), 'all');

        foreach ($this->requireRevisionable()->getChangelog()->getAuthors() as $user) {
            $el->addOption(
                $user->getName(),
                (string)$user->getID()
            );
        }
    }

    private function injectType() : void
    {
        $el = $this->addElementSelect(RevisionableChangelogScreenInterface::FILTER_TYPE, t('Type of change'));

        $el->addOption(t('All'), 'all');

        foreach ($this->requireRevisionable()->getChangelog()->getTypes() as $type => $label) {
            $el->addOption($label, $type);
        }
    }

    private function injectSearch() : void
    {
        $el = $this->addElementText(RevisionableChangelogScreenInterface::FILTER_SEARCH, t('Search terms'));
        $el->addFilter('strip_tags');
        $el->addFilterTrim();
    }

    // endregion

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $revisionable = $this->requireRevisionable();

        $this->ui->addJavascript('changelog.js');
        $this->ui->addJavascript('changelog/dialog/entry.js');
        $this->ui->addJavascript('changelog/dialog/switchrevision.js');

        $this->ui->addJavascriptHeadVariable(
            'Changelog.Revisionable',
            array(
                'Label' => $revisionable->getLabel(),
                'TypeName' => $revisionable->getRecordTypeName(),
                'PrettyRevision' => $revisionable->getPrettyRevision(),
                'CurrentRevision' => $revisionable->getRevision(),
                'LatestRevision' => $revisionable->getLatestRevision(),
                'Table' => $revisionable->getChangelogTable(),
                'OwnerPrimary' => $revisionable->getChangelogItemPrimary()
            )
        );

        $items = array();
        $filters = $this->createFilters();
        $this->dataGrid->configureFromFilters($filters);
        $entries = $filters->getItemsObjects();
        foreach ($entries as $entry) {
            $items[] = $this->collectEntry($entry);
        }

        return $this->renderer
            ->appendDataGrid(
                $this->dataGrid,
                $items
            );
    }

    // region: Data grid

    protected UI_DataGrid $dataGrid;

    protected function createDataGrid(): void
    {
        $grid = $this->ui->createDataGrid($this->getListID());

        $grid->enableCompactMode();
        $grid->enableLimitOptionsDefault();
        $grid->enableColumnControls(4);
        $grid->setEmptyMessage(t('No changes found.'));
        $grid->addHiddenScreenVars();
        $grid->addHiddenVars(array_merge($this->requireRevisionable()->getChangelogItemPrimary(), $this->getPersistVars()));

        $this->registerColumns($grid);


        $this->dataGrid = $grid;
    }

    protected function registerColumns(UI_DataGrid $grid) : void
    {
        $grid->addColumn(RevisionableChangelogScreenInterface::COL_REVISION, t('Revision'))
            ->setNowrap()
            ->setCompact()
            ->alignRight();

        $grid->addColumn(RevisionableChangelogScreenInterface::COL_DATE, t('Date'))
            ->setNowrap()
            ->setCompact();

        $grid->addColumn(RevisionableChangelogScreenInterface::COL_AUTHOR, t('Author'))
            ->setNowrap()
            ->setCompact();

        $grid->addColumn(RevisionableChangelogScreenInterface::COL_DETAILS, t('Details'));
        $grid->addColumn(RevisionableChangelogScreenInterface::COL_TYPE, t('Change type'));

        $grid->addColumn(RevisionableChangelogScreenInterface::COL_ACTIONS, '')
            ->setCompact()
            ->roleActions();
    }

    protected function collectEntry(Application_Changelog_Entry $entry) : array
    {
        $dbEntry = $entry->getDBEntry();

        return array(
            RevisionableChangelogScreenInterface::COL_CHANGELOG_ID => $entry->getID(),
            RevisionableChangelogScreenInterface::COL_REVISION => '<span class="monospace">'.$dbEntry->getInt($this->requireRevisionable()->getCollection()->getRevisionKeyName()).'</span>',
            RevisionableChangelogScreenInterface::COL_AUTHOR => $entry->getAuthorName(),
            RevisionableChangelogScreenInterface::COL_DATE => $entry->getDatePretty(true),
            RevisionableChangelogScreenInterface::COL_DETAILS => $entry->getText(),
            RevisionableChangelogScreenInterface::COL_ACTIONS => $this->renderEntryActions($entry),
            RevisionableChangelogScreenInterface::COL_TYPE => $entry->getTypeLabel()
        );
    }

    protected function renderEntryActions(Application_Changelog_Entry $entry) : string
    {
        if (!$entry->hasDiff()) {
            return '';
        }

        $this->ui->addJavascriptHeadStatement(
            'Changelog.Add',
            $entry->getID(),
            $entry->getAuthorName(),
            $entry->getDatePretty(true),
            $entry->getText(),
            $entry->getValueBefore(),
            $entry->getValueAfter()
        );

        return (string)UI::button()
            ->setIcon(UI::icon()->view())
            ->makeMini()
            ->click("Changelog.DialogDetails('" . $entry->getID() . "')");
    }

    // endregion

    // region: Apply filters

    /**
     * Creates and configures the filter criteria object instance
     * used to retrieve the changelog entries.
     *
     * @return Application_Changelog_FilterCriteria
     */
    protected function createFilters(): Application_Changelog_FilterCriteria
    {
        $changelog = $this->requireRevisionable()->getChangelog();
        $filters = $changelog->getFilters();
        $config = $this->getFiltersConfig();

        $this->applyFilters($filters, $config);
        $this->applyCustomFilters($filters, $config);

        return $filters;
    }

    private function applyFilters(Application_Changelog_FilterCriteria $filters, ArrayDataCollection $values) : void
    {
        $authorID = $values->getString(RevisionableChangelogScreenInterface::FILTER_AUTHOR);
        if (!empty($authorID) && $authorID !== 'all') {
            $filters->limitByAuthorID((int)$authorID);
        }

        $typeID = $values->getString(RevisionableChangelogScreenInterface::FILTER_TYPE);
        if (!empty($typeID) && $typeID !== 'all') {
            $filters->limitByType($typeID);
        }

        $revision = $values->getInt(RevisionableChangelogScreenInterface::FILTER_REVISION);
        if($revision > 0) {
            $filters->limitByCustomField($this->requireRevisionable()->getCollection()->getRevisionKeyName(), $revision);
        }

        $search = $values->getString(RevisionableChangelogScreenInterface::FILTER_SEARCH);
        if (!empty($search)) {
            $filters->setSearch($search);
        }

        $dateTo = $values->getString(RevisionableChangelogScreenInterface::FILTER_TO_DATE);
        if(!empty($dateTo)) {
            $filters->limitByDateTo(Microtime::createFromString($dateTo));
        }

        $dateFrom = $values->getString(RevisionableChangelogScreenInterface::FILTER_FROM_DATE);
        if(!empty($dateFrom)) {
            $filters->limitByDateFrom(Microtime::createFromString($dateFrom));
        }
    }

    protected function applyCustomFilters(Application_Changelog_FilterCriteria $filters, ArrayDataCollection $values) : void
    {

    }

    protected function getListID(): string
    {
        return 'changelog-'.$this->requireRevisionable()->getRecordTypeName();
    }

    protected function getDefaultFilters() : array
    {
        return array(
            RevisionableChangelogScreenInterface::FILTER_AUTHOR => 'all',
            RevisionableChangelogScreenInterface::FILTER_TYPE => 'all',
            RevisionableChangelogScreenInterface::FILTER_SEARCH => '',
            RevisionableChangelogScreenInterface::FILTER_REVISION => '',
            RevisionableChangelogScreenInterface::FILTER_FROM_DATE => '',
            RevisionableChangelogScreenInterface::FILTER_TO_DATE => ''
        );
    }

    protected function getFiltersConfig(): ArrayDataCollection
    {
        $config = $this->getDefaultFilters();

        $raw = $this->user->getSetting($this->getListID());
        if (!empty($raw)) {
            $config = array_merge($config, ConvertHelper::json2array($raw));
        }

        return ArrayDataCollection::create($config);
    }

    protected function handle_filtersSubmitted(): void
    {
        $values = $this->getFormValues();
        $filters = array();

        foreach(array_keys($this->getDefaultFilters()) as $key) {
            $filters[$key] = $values[$key] ?? null;
        }

        $this->user->setSetting($this->getListID(), JSONConverter::var2json($filters));
        $this->user->saveSettings();

        $redirectParams = $this->requireRevisionable()->getChangelogItemPrimary();
        $redirectParams = array_merge($redirectParams, $this->getPageParams());

        $this->redirectWithSuccessMessage(
            t('The filters you selected have been applied successfully at %1$s.', date('H:i:s')),
            $redirectParams
        );
    }

    // endregion

    /**
     * @return array<string, string|int>
     */
    abstract protected function getPersistVars() : array;

    public function getURL(array $params = array()) : string
    {
        return parent::getURL(array_merge(
            $params,
            $this->requireRevisionable()->getChangelogItemPrimary(),
            $this->getPersistVars()
        ));
    }

    public function getResetURL(array $params=array()) : string
    {
        $params[RevisionableChangelogScreenInterface::REQUEST_PARAM_RESET] = 'yes';
        return $this->getURL($params);
    }
}
