<?php

define('REVISIONABLE_CHANGELOG_ERROR_NOT_A_VALID_REVISIONABLE', 630001);

/**
 * 
 * @property UI_Themes_Theme_ContentRenderer $renderer
 * @property UI_Page $page
 * @property Application_Request $request
 * @property Application_Driver $driver
 * @property Application_User $user
 * @property UI $ui
 */
trait Application_Traits_Admin_RevisionableChangelog
{
    /**
     * @var Application_RevisionableStateless
     */
    protected $revisionable;
  
    /**
     * @var UI_DataGrid
     */
    protected $datagrid;

    /**
     * @var UI_Form
     */
    protected $filterForm;
    
    abstract protected function getRevisionable();
    
    abstract protected function isUserAuthorized();
    
    public function getURLName() : string
    {
        return 'changelog';
    }
    
    public function getTitle() : string
    {
        return t('Changelog');
    }
    
    public function getNavigationTitle() : string
    {
        return t('Changelog');
    }
    
    protected function init() : void
    {
        parent::init();
        
        if(!$this->isAdminMode()) {
            return;
        }
        
        $this->revisionable = $this->getRevisionable();
        
        if(!$this->revisionable instanceof Application_RevisionableStateless) {
            throw new Application_Exception(
                'Not a valid revisionable',
                sprintf(
                    'The specified variable is not a class extending the [%s] class, and cannot be used to access a changelog.',
                    'Application_RevisionableStateless'
                ),
                REVISIONABLE_CHANGELOG_ERROR_NOT_A_VALID_REVISIONABLE
            );
        }
    }
    
    protected function _handleActions() : bool
    {
        $this->selectRevision();
        
        $this->createDatagrid();
        $this->createFilterForm();
        
        if($this->filterForm->isSubmitted() && $this->filterForm->validate()) {
            $this->handle_filtersSubmitted();
        }

        return true;
    }
    
    protected function selectRevision()
    {
        $this->request->registerParam('revision')->setInteger();
        $revision = $this->request->getParam('revision');
        if(!empty($revision) && $this->revisionable->revisionExists($revision)) {
            $this->revisionable->selectRevision($revision);
        }
    }
    
    protected function _handleSidebar() : void
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
    
    protected function createFilterForm()
    {
        $changelog = $this->revisionable->getChangelog();
        
        $wrapper = $this->configureForm('changelog-filters', $this->getFiltersConfig());
        $form = $wrapper->getForm();
        
        $authors = $changelog->getAuthors();
        $authorEl = $form->addSelect('author');
        $authorEl->setLabel(t('Author'));
        $authorEl->addOption(t('All'), 'all');
        foreach($authors as $user) {
            $authorEl->addOption(
                $user->getName(), 
                (string)$user->getID()
            );
        }
        
        $types = $changelog->getTypes();
        $typeEl = $form->addSelect('type');
        $typeEl->setLabel(t('Type of change'));
        $typeEl->addOption(t('All'), 'all');
        foreach($types as $type => $label) {
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
        $this->datagrid->configureFromFilters($filters);
        $entries = $filters->getItemsObjects();
        foreach($entries as $entry)
        {
            $actions = '';
            if($entry->hasDiff()) {
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
                'author' => $entry->getAuthorName(),
                'date' => $entry->getDatePretty(true),
                'details' => $entry->getText(),
                'actions' => $actions
            );
        }

        return $this->renderer
        ->appendDataGrid(
            $this->datagrid,
            $items
        );
    }
    
    protected function createDatagrid()
    {
        $grid = $this->ui->createDataGrid($this->revisionable->getRevisionableTypeName().'_changelog');
        $grid->enableCompactMode();
        $grid->addColumn('date', t('Date'))->setNowrap();
        $grid->addColumn('author', t('Author'))->setNowrap();
        $grid->addColumn('details', t('Details'));
        $grid->addColumn('actions', '')->setCompact()->roleActions();
        
        $grid->setEmptyMessage(
            t('No changes found in this revision.')
        );
        
        $grid->setTitle(t(
            'Revision %1$s, created on %2$s by %3$s',
            $this->revisionable->getPrettyRevision(),
            AppUtils\ConvertHelper::date2listLabel($this->revisionable->getRevisionDate(), true),
            $this->revisionable->getOwnerName()
        ));
        
        $grid->enableLimitOptionsDefault();
        
        $grid->addHiddenVars($this->getPageParams());
        $grid->addHiddenVar('revision', $this->revisionable->getRevision());
        
        $primary = $this->revisionable->getChangelogItemPrimary();
        foreach($primary as $name => $value) {
            $grid->addHiddenVar($name, $value);
        }
        
        $this->datagrid = $grid;
    }
    
    /**
     * Creates and configures the filters object instance that is
     * used to retrieve the changelog entries.
     *
     * @return Application_Changelog_FilterCriteria
     */
    protected function createFilters()
    {
        $changelog = $this->revisionable->getChangelog();
        $filters = $changelog->getFilters();
        
        $config = $this->getFiltersConfig();
        if($config['author'] != 'all') {
            $filters->limitByAuthorID($config['author']);
        }
        
        if($config['type'] != 'all') {
            $filters->limitByType($config['type']);
        }
        
        if(!empty($config['search'])) {
            $filters->setSearch($config['search']);
        }
        
        return $filters;
    }
    
    protected function getFiltersVar()
    {
        return md5(get_class($this)).'-'.$this->revisionable->getRevision();
    }
    
    protected function getFiltersConfig()
    {
        $config = array(
            'author' => 'all',
            'type' => 'all',
            'search' => ''
        );
        
        $raw = $this->user->getSetting($this->getFiltersVar());
        if(!empty($raw)) {
            $config = json_decode($raw, true);
        }
        
        return $config;
    }
    
    protected function handle_filtersSubmitted()
    {
        $values = $this->filterForm->getValues();
        
        $filters = array(
            'author' => $values['author'],
            'type' => $values['type'],
            'search' => $values['search']
        );
        
        $this->user->setSetting($this->getFiltersVar(), json_encode($filters));
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
