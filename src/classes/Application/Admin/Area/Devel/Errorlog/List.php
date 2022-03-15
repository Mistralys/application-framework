<?php

use AppUtils\FileHelper;

class Application_Admin_Area_Devel_Errorlog_List extends Application_Admin_Area_Mode_Submode
{
    public function getURLName() : string
    {
        return 'list';
    }

    public function getTitle() : string
    {
        return t('Error log');
    }

    public function getNavigationTitle() : string
    {
        return t('List');
    }

    public function getDefaultAction() : string
    {
        return '';
    }

   /**
    * @var Application_ErrorLog
    */
    protected $errorlog;
    
    protected function _handleActions() : bool
    {
        $this->errorlog = Application::createErrorLog();
        
        if($this->request->getBool('deleteall')) {
            $this->handle_deleteAll();
        }
        
        if($this->request->getBool('trigger_exception')) {
            $this->handle_triggerException();
        }
        
        $this->createDatagrid();

        return true;
    }

    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->mode->getNavigationTitle())->makeLinkedFromMode($this->mode);
    }
    
    protected function _renderContent()
    {
        $entries = array();
        $logs = $this->errorlog->getLogs();
        
        foreach($logs as $log) 
        {
            $entries[] = array(
                'id' => $log->getID(),
                'monthnumber' => $log->getMonthNumber(),
                'month' => '<a href="'.$log->getAdminViewURL().'">'.$log->getMonthName().'</a>',
                'size' => $log->getFileSizePretty(),
                'bytesize' => $log->getFileSize()
            );
        }

        return $this->renderDatagrid(
            t('Error logs for %1$s', $this->errorlog->getYear()),
            $this->datagrid,
            $entries,
            true
        );
    }
    
    protected function _handleSidebar() : void
    {
        $this->sidebar->addButton('clear_all', t('Delete all'))
        ->makeDangerous()
        ->makeConfirm(t('This will delete all logfiles for %1$s.', $this->errorlog->getYear()))
        ->setTooltip(t('This will delete all logfiles for %1$s.', $this->errorlog->getYear()))
        ->makeLinked($this->errorlog->getAdminDeleteAllURL());
        
        $this->sidebar->addSeparator();
        
        $panel = $this->sidebar->addDeveloperPanel();

        $panel->addButton(
            UI::button(t('%1$s exception', 'PHP'))
            ->link($this->errorlog->getAdminTriggerExceptionURL())
        );
        
        $panel->addButton(
            UI::button(t('%1$s exception', 'PHP').' (incl. previous)')
            ->link($this->errorlog->getAdminTriggerExceptionURL(array('prev' => 'yes')))
        );

        $panel->addButton(
            UI::button('PHP AppUtils BaseException')
                ->link($this->errorlog->getAdminTriggerExceptionURL(array('base' => 'yes')))
        );

        $panel->addButton(
            UI::button('Connector exception')
                ->link($this->errorlog->getAdminTriggerExceptionURL(array('connector' => 'yes')))
        );

        $panel->addButton(
            UI::button(t('%1$s exception', 'JavaScript'))
            ->click('throw new ApplicationException(\'Test JavaScript exception\', \'Developer info details\', 42903)')
        );
        
        $panel->addButton(
            UI::button(t('%1$s exception', 'JavaScript').' ('.t('Generic').')')
            ->click('throw new Error(\'Test generic exception class\')')
        );
        
        $panel->addButton(
            UI::button(t('%1$s exception', 'AJAX'))
            ->click('application.createAJAX(\'ThrowError\').Send()')
        );
    }

    /**
     * @var UI_DataGrid
     */
    protected $datagrid;

    protected function createDatagrid() : void
    {
        $grid = $this->ui->createDataGrid('errorlogs');
        $grid->configureForScreen($this);

        $grid->configureForScreen($this);

        $grid->addColumn('month', t('Month'))
        ->setSortable(true)
        ->setSortingNumeric('monthnumber');
        
        $grid->addColumn('size', t('Size'))
        ->setSortable()
        ->setSortingNumeric('bytesize');
        
        $grid->enableMultiSelect('id');
        
        $grid->addConfirmAction(
            'delete', 
            t('Delete...'), 
            t('This will delete the log file for the month.').' '.
            t('This cannot be undone, are you sure?')
        )
        ->setIcon(UI::icon()->delete())
        ->makeDangerous()
        ->setCallback(array($this, 'handle_multiDelete'));
        
        $this->datagrid = $grid;
    }
    
    protected function handle_deleteAll()
    {
        $logs = $this->errorlog->getFolder();
        
        FileHelper::deleteTree($logs);
        
        $this->redirectWithSuccessMessage(
            t(
                'All error logs for %1$s have been deleted successfully at %2$s.',
                $this->errorlog->getYear(),
                date('H:i:s')
            ), 
            $this->errorlog->getAdminListURL()
        );
    }
    
    public function handle_multiDelete(UI_DataGrid_Action $action, $ids)
    {
        foreach($ids as $eid) 
        {
            $log = $this->errorlog->getByID($eid);
            
            FileHelper::deleteFile($log->getFilePath());
        }
        
        $this->redirectWithSuccessMessage(
            UI::icon()->ok().' '.
            t('The selected error logs have been deleted at %1$s.', date('H:i:s')), 
            $this->getURL()
        );
    }

    protected function handle_triggerException()
    {
        try
        {
            $prev = null;
            
            if($this->request->getBool('prev')) 
            {
                $prev = new Exception(
                    'Test previous exception',
                    42902
                );
            }

            if($this->request->getBool('base'))
            {
                throw new \AppUtils\BaseException(
                    'Errorlog test AppUtils base exception',
                    'This is an exception triggered on purpose in the errorlogs admin.',
                    42901,
                    $prev
                );
            }
            else if($this->request->getBool('connector'))
            {
                Connectors::createDummyConnector()->executeFailRequest();
            }
            else
            {
                throw new Application_Exception(
                    'Errorlog test exception',
                    'This is an exception triggered on purpose in the errorlogs admin.',
                    42904,
                    $prev
                );
            }
        }
        catch(Exception $e)
        {
            $e = Application_Bootstrap::convertException($e);
            $e->log();
        }
    
        $this->redirectWithSuccessMessage(
            t('Exception triggered successfully at %1$s.', date('H:i:s')),
            $this->errorlog->getAdminListURL()
        );
    }
}
