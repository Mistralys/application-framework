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
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
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
    protected UI_DataGrid $dataGrid;
    protected UI_Form $filterForm;

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

        if ($this->filterForm->isSubmitted() && $this->filterForm->validate()) {
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
        $section->appendContent($this->filterForm->renderHorizontal());
    }

    // region: Filter form

    protected function createFilterForm(): void
    {
        $changelog = $this->revisionable->getChangelog();

        $wrapper = $this->configureForm('changelog-filters', $this->getFiltersConfig());
        $this->filterForm = $wrapper;

        $this->injectAuthor();
        $this->injectType();
        $this->injectSearch();
        $this->injectButtons();

        $wrapper->makeCondensed();
        $wrapper->addHiddenVars($this->getPageParams());
        $wrapper->addHiddenVars($changelog->getPrimary());

        $this->filterForm = $wrapper;
    }

    private function injectButtons() : void
    {
        $this->filterForm->addButton('filter')
            ->setIcon(UI::icon()->filter())
            ->setLabel(t('Filter the list'))
            ->makeSubmit();

        $this->filterForm->addButton('reset')
            ->setIcon(UI::icon()->reset())
            ->setLabel(t('Reset'))
            ->link($this->getResetURL());
    }

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

    private function injectAuthor() : void
    {
        $el = $this->filterForm->addSelect(RevisionableChangelogScreenInterface::FILTER_AUTHOR, t('Author'));

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
        $el = $this->filterForm->addSelect(RevisionableChangelogScreenInterface::FILTER_TYPE, t('Type of change'));

        $el->addOption(t('All'), 'all');

        foreach ($this->revisionable->getChangelog()->getTypes() as $type => $label) {
            $el->addOption($label, $type);
        }
    }

    private function injectSearch() : void
    {
        $el = $this->filterForm->addText(RevisionableChangelogScreenInterface::FILTER_SEARCH, t('Search terms'));
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
        if ($config[RevisionableChangelogScreenInterface::FILTER_AUTHOR] !== 'all') {
            $filters->limitByAuthorID($config[RevisionableChangelogScreenInterface::FILTER_AUTHOR]);
        }

        if ($config[RevisionableChangelogScreenInterface::FILTER_TYPE] !== 'all') {
            $filters->limitByType($config[RevisionableChangelogScreenInterface::FILTER_TYPE]);
        }

        if (!empty($config[RevisionableChangelogScreenInterface::FILTER_SEARCH])) {
            $filters->setSearch($config[RevisionableChangelogScreenInterface::FILTER_SEARCH]);
        }

        $this->applyCustomFilters($filters);

        return $filters;
    }

    protected function applyCustomFilters(Application_Changelog_FilterCriteria $filters) : void
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
            RevisionableChangelogScreenInterface::FILTER_SEARCH => ''
        );
    }

    protected function getFiltersConfig(): array
    {
        $config = $this->getDefaultFilters();

        $raw = $this->user->getSetting($this->getListID());
        if (!empty($raw)) {
            $config = array_merge($config, ConvertHelper::json2array($raw));
        }

        return $config;
    }

    protected function handle_filtersSubmitted(): void
    {
        $values = $this->filterForm->getValues();
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
}
