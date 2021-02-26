<?php
/**
 * File containing the {@link Application_Admin_Area_Devel_Dbdump} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Area_Devel_Dbdump
 */

/**
 * Developer helper to handle database dumps: creates dumps using
 * the bundled shell script (Linux only), and offers them for
 * download in the user interface, along with a history of previous
 * dumps.
 *
 * @package Application
 * @subpackage Administration
 * @author Andreas Martin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Admin_Area_Devel_Dbdump extends Application_Admin_Area_Mode
{
   /**
    * @var UI_DataGrid
    */
    protected $datagrid;

   /**
    * @var Application_DBDumps
    */
    protected $dumps;
    
    public function getURLName()
    {
        return 'dbdump';
    }

    public function getTitle()
    {
        return t('Database dumps');
    }

    public function getNavigationTitle()
    {
        return t('Database dumps');
    }

    public function getDefaultSubmode()
    {
        return null;
    }

    public function isUserAllowed()
    {
        return $this->user->isDeveloper();
    }

    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }
    
    protected function _handleActions()
    {
        $this->dumps = $this->driver->createDBDumps();
        
        $this->createDataGrid();
        
        if($this->request->getBool('confirm')) 
        {
            $this->createDump();
        } 
        else if($this->request->getBool('download')) 
        {
            $this->downloadDump();
        }
    }

    protected function downloadDump()
    {
        $id = $this->request->registerParam('dump_id')->setInteger()->setCallback(array($this->dumps, 'dumpExists'))->get();
        if(empty($id)) {
            $this->redirectWithInfoMessage(
                t('No such database dump found.'), 
                $this->getURL()
            );
        }
        
        $dump = $this->dumps->getByID($id);
        $dump->sendFile();
    }

    protected function createDump()
    {
        $dump = $this->dumps->createDump();

        $this->ui->addSuccessMessage(t(
            'The dump %1$s has been successfully created at %2$s.', 
            '<b>'.$dump->getID().'</b>', 
            date('H:i:s')
        ));

        if($this->request->getBool('download')) {
            $dump->sendFile();
        }
        
        $this->redirectTo($this->getURL());
    }

    protected function _renderContent()
    {
        $dumps = $this->dumps->getAll(); 

        $entries = array();
        foreach($dumps as $dump) {
            $entries[] = array(
                'selected' => $dump->getID(),
                'name' => '<a href="'.$dump->getURLDownload().'">'.$dump->getDatePretty().'</a>',
                'size' => $dump->getFileSizePretty()
            );
        }

        return $this->renderDatagrid(
            t('Available database dumps'),
            $this->datagrid,
            $entries
        );
    }

    protected function _handleSidebar()
    {
        $this->sidebar->addButton('create', t('Create new dump'))
        ->setIcon(UI::icon()->add())
        ->makeLinked($this->getURL(array('confirm' => 'yes')))
        ->makePrimary();

        $this->sidebar->addButton('create_dl', t('Create and download'))
        ->setIcon(UI::icon()->download())
        ->makeLinked($this->getURL(array('confirm' => 'yes', 'download' => 'yes')));
        
        if(isOSWindows()) {
            $this->sidebar->addSeparator();
            
            $this->sidebar->addInfoMessage(
                '<b>'.t('Note:').'</b> '.
                t(
                    'Please ensure that the %1$s executable is accessible in the system %2$s variable.',
                    '<code>mysqldump.exe</code>',
                    '<code>PATH</code>'
                )
            );
        }
    }

    private function createDatagrid()
    {
        $grid = $this->ui->createDataGrid('dumps');
        $grid->enableMultiSelect('selected');
        $grid->addColumn('selected', t('ID'))->setCompact();
        $grid->addColumn('name', t('Created on'));
        $grid->addColumn('size', t('Size'));
        $grid->addConfirmAction(
            'delete',
            t('Delete...'),
            t('The selected dumps will be deleted.').' '.t('This cannot be undone, are you sure?')
        )
        ->makeDangerous()
        ->setIcon(UI::icon()->delete())
        ->setCallback(array($this, 'handle_multiDelete'));

        $this->datagrid = $grid;
    }
    
    public function handle_multiDelete(UI_DataGrid_Action $action, $ids)
    {
        $deleted = 0;
        
        foreach($ids as $id)
        {
            $dump = $this->dumps->getByID($id);
            $dump->delete();
            $deleted++;
        }
        
        if ($deleted == 1)
        {
            $this->redirectWithSuccessMessage(
                t(
                    'The dump has been successfully deleted at %1$s.',
                    date('H:i:s')
                ),
                $this->getURL()
            );
        }
        else if($deleted > 1)
        {
            $this->redirectWithSuccessMessage(
                t(
                    '%1$s dumps have been successfull deleted at %2$s.',
                    $deleted,
                    date('H:i:s')
                ),
                $this->getURL()
            );
        }
        
        $this->redirectWithInfoMessage(
            UI::icon()->information().' '.
            '<b>'.t('No dumps deleted:').'</b> '.
            t('No dumps were selected.'), 
            $this->getURL()
        );
    }
}