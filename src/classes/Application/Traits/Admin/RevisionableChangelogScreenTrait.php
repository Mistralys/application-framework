<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Traits\Admin;

use Application\Interfaces\Admin\RevisionableChangelogScreenInterface;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableStatelessInterface;
use Application_Changelog_Entry;
use Application_Changelog_FilterCriteria;
use Application_RevisionableCollection;
use Application_RevisionableStateless;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use Closure;
use UI;
use UI_DataGrid;
use UI_Form;

/**
 * @package Application
 * @subpackage Revisionables
 *
 * @see RevisionableChangelogScreenInterface
 */
trait RevisionableChangelogScreenTrait
{
    protected RevisionableStatelessInterface $revisionable;

    abstract protected function getRevisionable(): RevisionableStatelessInterface;

    abstract protected function isUserAuthorized(): bool;

    public function getTitle(): string
    {
        return t('Changelog');
    }

    public function getNavigationTitle(): string
    {
        return t('Changelog');
    }

    protected function init(): void
    {
        parent::init();

        if (!$this->isAdminMode()) {
            return;
        }

        $this->revisionable = $this->getRevisionable();

        if (!$this->revisionable instanceof Application_RevisionableStateless) {
            throw new RevisionableException(
                'Not a valid revisionable',
                sprintf(
                    'The specified variable is not a class extending the [%s] class, and cannot be used to access a changelog.',
                    'Application_RevisionableStateless'
                ),
                RevisionableChangelogScreenInterface::REVISIONABLE_CHANGELOG_ERROR_NOT_A_VALID_REVISIONABLE
            );
        }
    }

    protected function _handleActions(): bool
    {
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
        $changelog = $this->revisionable->getChangelog();

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
            Closure::fromCallable(array($this, 'validateScheduleDates')),
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
            ->addFilter('strip_tags')
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

        foreach ($this->revisionable->getChangelog()->getAuthors() as $user) {
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

        foreach ($this->revisionable->getChangelog()->getTypes() as $type => $label) {
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

    protected function _renderContent()
    {
        /* @var $entry Application_Changelog_Entry */

        if (!$this->isUserAuthorized()) {
            return $this->renderUnauthorized();
        }

        $this->ui->addJavascript('changelog.js');
        $this->ui->addJavascript('changelog/dialog/entry.js');
        $this->ui->addJavascript('changelog/dialog/switchrevision.js');

        $this->ui->addJavascriptHeadVariable(
            'Changelog.Revisionable',
            array(
                'Label' => $this->revisionable->getLabel(),
                'TypeName' => $this->revisionable->getRevisionableTypeName(),
                'PrettyRevision' => $this->revisionable->getPrettyRevision(),
                'CurrentRevision' => $this->revisionable->getRevision(),
                'LatestRevision' => $this->revisionable->getLatestRevision(),
                'Table' => $this->revisionable->getChangelogTable(),
                'OwnerPrimary' => $this->revisionable->getChangelogItemPrimary()
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
        $grid->addHiddenVars($this->revisionable->getChangelogItemPrimary());

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
            RevisionableChangelogScreenInterface::COL_REVISION => '<span class="monospace">'.$dbEntry->getInt($this->revisionable->getCollection()->getRevisionKeyName()).'</span>',
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
        $changelog = $this->revisionable->getChangelog();
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
            $filters->limitByCustomField($this->revisionable->getCollection()->getRevisionKeyName(), $revision);
        }

        $search = $values->getString(RevisionableChangelogScreenInterface::FILTER_SEARCH);
        if (!empty($search)) {
            $filters->setSearch($search);
        }

        $dateTo = $values->getMicrotime(RevisionableChangelogScreenInterface::FILTER_TO_DATE);
        if($dateTo !== null) {
            $filters->limitByDateTo($dateTo);
        }

        $dateFrom = $values->getMicrotime(RevisionableChangelogScreenInterface::FILTER_FROM_DATE);
        if($dateFrom !== null) {
            $filters->limitByDateFrom($dateFrom);
        }
    }

    protected function applyCustomFilters(Application_Changelog_FilterCriteria $filters, ArrayDataCollection $values) : void
    {

    }

    protected function getListID(): string
    {
        return 'changelog-'.$this->revisionable->getRevisionableTypeName();
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

        $redirectParams = $this->revisionable->getChangelogItemPrimary();
        $redirectParams = array_merge($redirectParams, $this->getPageParams());

        $this->redirectWithSuccessMessage(
            t('The filters you selected have been applied successfully at %1$s.', date('H:i:s')),
            $redirectParams
        );
    }

    // endregion

    public function getURL(array $params = array()) : string
    {
        return parent::getURL(array_merge(
            $params,
            $this->revisionable->getChangelogItemPrimary()
        ));
    }

    public function getResetURL(array $params=array()) : string
    {
        $params[RevisionableChangelogScreenInterface::REQUEST_PARAM_RESET] = 'yes';
        return $this->getURL($params);
    }
}
