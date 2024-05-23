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
use Application_RevisionableStateless;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use UI;
use UI_DataGrid;
use UI_Form;

/**
 * @package Application
 * @subpackage Revisionables
 */
trait RevisionableChangelogScreenTrait
{
    public const COL_DATE = 'date';
    public const COL_AUTHOR = 'author';
    public const COL_DETAILS = 'details';
    public const COL_TYPE = 'type';
    public const COL_ACTIONS = 'actions';
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
        $this->selectRevision();

        $this->createDataGrid();
        $this->createFilterForm();

        if ($this->filterForm->isSubmitted() && $this->filterForm->validate()) {
            $this->handle_filtersSubmitted();
        }

        return true;
    }

    protected function selectRevision(): void
    {
        $this->request->registerParam('revision')->setInteger();
        $revision = $this->request->getParam('revision');
        if (!empty($revision) && $this->revisionable->revisionExists($revision)) {
            $this->revisionable->selectRevision($revision);
        }
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('switch_revision', t('Switch revision...'))
            ->makePrimary()
            ->setIcon(UI::icon()->changelog())
            ->makeClickable('Changelog.DialogSwitchRevision()');

        $this->sidebar->addSeparator();

        $section = $this->sidebar->addSection();
        $section->setTitle(t('Filter the list'));
        $section->appendContent($this->filterForm->renderHorizontal());


        $this->sidebar->addHelp(
            t('Filtering help'),
            '<ul>' .
            '<li>' . t('The author and type of change filters only show those available in the selected revision.') . '</li>' .
            '<li>' . t('The search works on any element names shown in the changelog.') . '</li>' .
            '<li>' . t('The filter settings are automatically saved, but separately for each revision.') . '</li>' .
            '</ul>'
        );
    }

    protected function createFilterForm(): void
    {
        $changelog = $this->revisionable->getChangelog();

        $wrapper = $this->configureForm('changelog-filters', $this->getFiltersConfig());
        $form = $wrapper->getForm();

        $authors = $changelog->getAuthors();
        $authorEl = $form->addSelect('author');
        $authorEl->setLabel(t('Author'));
        $authorEl->addOption(t('All'), 'all');
        foreach ($authors as $user) {
            $authorEl->addOption(
                $user->getName(),
                (string)$user->getID()
            );
        }

        $types = $changelog->getTypes();
        $typeEl = $form->addSelect('type');
        $typeEl->setLabel(t('Type of change'));
        $typeEl->addOption(t('All'), 'all');
        foreach ($types as $type => $label) {
            $typeEl->addOption($label, $type);
        }

        $searchEl = $form->addText('search');
        $searchEl->setLabel(t('Search terms'));
        $searchEl->addFilter('trim');
        $searchEl->addFilter('strip_tags');

        $button = $form->addButton('filter');
        $button->setContent(UI::icon()->filter() . ' ' . t('Filter the list'));
        $button->setAttribute('type', 'submit');
        $button->addClass('btn btn-default');

        $wrapper->makeCondensed();
        $wrapper->addHiddenVars($this->getPageParams());
        $wrapper->addHiddenVars($changelog->getPrimary());
        $wrapper->addHiddenVar('revision', (string)$this->revisionable->getRevision());

        $this->filterForm = $wrapper;
    }

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
            $actions = '';
            if ($entry->hasDiff()) {
                $actions = UI::button()
                    ->setIcon(UI::icon()->view())
                    ->makeMini()
                    ->click("Changelog.DialogDetails('" . $entry->getID() . "')");

                $this->ui->addJavascriptHeadStatement(
                    'Changelog.Add',
                    $entry->getID(),
                    $entry->getAuthorName(),
                    $entry->getDatePretty(true),
                    $entry->getText(),
                    $entry->getValueBefore(),
                    $entry->getValueAfter()
                );
            }

            $items[] = array(
                'changelog_id' => $entry->getID(),
                self::COL_AUTHOR => $entry->getAuthorName(),
                self::COL_DATE => $entry->getDatePretty(true),
                self::COL_DETAILS => $entry->getText(),
                self::COL_ACTIONS => $actions,
                self::COL_TYPE => $entry->getTypeLabel()
            );
        }

        return $this->renderer
            ->appendDataGrid(
                $this->dataGrid,
                $items
            );
    }

    protected function createDataGrid(): void
    {
        $grid = $this->ui->createDataGrid($this->revisionable->getRevisionableTypeName() . '_changelog');
        $grid->enableCompactMode();
        $grid->addColumn(self::COL_DATE, t('Date'))->setNowrap();
        $grid->addColumn(self::COL_AUTHOR, t('Author'))->setNowrap();
        $grid->addColumn(self::COL_DETAILS, t('Details'));
        $grid->addColumn(self::COL_TYPE, t('Change type'));
        $grid->addColumn(self::COL_ACTIONS, '')->setCompact()->roleActions();

        $grid->setEmptyMessage(
            t('No changes found in this revision.')
        );

        $grid->setTitle(t(
            'Revision %1$s, created on %2$s by %3$s',
            $this->revisionable->getPrettyRevision(),
            ConvertHelper::date2listLabel($this->revisionable->getRevisionDate(), true),
            $this->revisionable->getOwnerName()
        ));

        $grid->enableLimitOptionsDefault();
        $grid->enableColumnControls(3);

        $grid->addHiddenVars($this->getPageParams());
        $grid->addHiddenVar('revision', $this->revisionable->getRevision());

        $primary = $this->revisionable->getChangelogItemPrimary();
        foreach ($primary as $name => $value) {
            $grid->addHiddenVar($name, $value);
        }

        $this->dataGrid = $grid;
    }

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
        if ($config['author'] !== 'all') {
            $filters->limitByAuthorID($config['author']);
        }

        if ($config['type'] !== 'all') {
            $filters->limitByType($config['type']);
        }

        if (!empty($config['search'])) {
            $filters->setSearch($config['search']);
        }

        return $filters;
    }

    protected function getFiltersVar(): string
    {
        return md5(get_class($this)) . '-' . $this->revisionable->getRevision();
    }

    protected function getFiltersConfig(): array
    {
        $config = array(
            'author' => 'all',
            'type' => 'all',
            'search' => ''
        );

        $raw = $this->user->getSetting($this->getFiltersVar());
        if (!empty($raw)) {
            $config = ConvertHelper::json2array($raw);
        }

        return $config;
    }

    protected function handle_filtersSubmitted(): void
    {
        $values = $this->filterForm->getValues();

        $filters = array(
            'author' => $values['author'],
            'type' => $values['type'],
            'search' => $values['search']
        );

        $this->user->setSetting($this->getFiltersVar(), JSONConverter::var2json($filters));
        $this->user->saveSettings();

        $redirectParams = $this->revisionable->getChangelogItemPrimary();
        $redirectParams['revision'] = $this->revisionable->getRevision();
        $redirectParams = array_merge($redirectParams, $this->getPageParams());

        $this->redirectWithSuccessMessage(
            t('The filters you selected have been applied successfully at %1$s.', date('H:i:s')),
            $redirectParams
        );
    }
}
