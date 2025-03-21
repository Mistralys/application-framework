<?php

use Application\AppFactory;

class Application_Admin_Area_Devel_Errorlog_View extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'view';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getTitle() : string
    {
        return t('View error log');
    }

    public function getNavigationTitle() : string
    {
        return t('View');
    }

    public function getDefaultAction() : string
    {
        return '';
    }
   
   /**
    * @var Application_ErrorLog
    */
    protected $errorlog;
    
   /**
    * 
    * @var Application_ErrorLog_Log
    */
    protected $log;
    
    protected ?Application_ErrorLog_Log_Entry $entry = null;
    
    protected function _handleActions() : bool
    {
        $this->errorlog = AppFactory::createErrorLog();
        $this->log = $this->errorlog->getByRequest();
        
        if($this->log === null) 
        {
            $this->redirectWithInfoMessage(
                t('No such error log found.'), 
                $this->errorlog->getAdminListURL()
            );
        }
        
        $this->entry = $this->log->getEntryByRequest();

        $this->createDatagrid();

        return true;
    }

    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->mode->getNavigationTitle())->makeLinkedFromMode($this->mode);
        $this->breadcrumb->appendItem($this->log->getMonthName())->makeLinked($this->log->getAdminViewURL());
        
        if(isset($this->entry)) 
        {
            $this->breadcrumb->appendItem(
                t(
                    '%1$s log entry registered %2$s', 
                    $this->entry->getTypeLabel(),
                    $this->entry->getTimePretty()
                )                
            );
        }
    }
    
    protected function _renderContent()
    {
        if(isset($this->entry)) 
        {
            return $this->renderEntry($this->entry);
        }
        
        $items = $this->log->getEntries();
        $entries = array();
        
        foreach($items as $item) 
        {
            $entries[] = array(
                'date' => '<a href="'.$item->getAdminViewURL().'">'.$item->getTimePretty().'</a>',
                'code' => $item->getCode(),
                'username' => $item->getUserName(),
                'type' => $item->getTypeLabel(),
                'message' => $item->getMessage(),
                'log' => $this->renderApplog($item),
                'referer' => $this->renderRefererLink($item),
            );
        }

        $this->ui->addJavascript('errorlog.js');

        return $this->renderDatagrid(
            t('Error log entries for %1$s %2$s', $this->log->getMonthName(), $this->log->getDate()->format('Y')),
            $this->datagrid,
            $entries,
            false
        );
    }
    
    protected function renderRefererLink(Application_ErrorLog_Log_Entry $entry)
    {
        if(!$entry->hasReferer()) {
            return UI::icon()->notAvailable()->makeMuted();
        }
        
        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $entry->getReferer(),
            t('Open')
        );
    }
    
    protected function renderApplog(Application_ErrorLog_Log_Entry $entry)
    {
        if(!$entry->hasApplog()) {
            return UI::icon()->notAvailable()->makeMuted();
        }
        
        return UI::icon()->ok()->makeSuccess();
    }
    
    protected function renderEntry(Application_ErrorLog_Log_Entry $entry) : string
    {
        $grid = $this->ui->createPropertiesGrid();
        
        $grid->addHeader(t('Basic error details'));
        $grid->add(t('Date'), $entry->getTime()->format('Y-m-d H:i:s'));
        $grid->add(t('User'), $entry->getUserName());
        $grid->add(t('Type'), $entry->getTypeLabel());
        $grid->add(t('Code'), $entry->getCode());
        $grid->add(t('Message'), $entry->getMessage());
        $grid->add(t('Referer'), $entry->getReferer());
        
        $grid->addHeader(t('%1$s details', $entry->getTypeLabel()));
        $entry->addProperties($grid);
        
        $content = $grid->render();
        
        if($entry->hasTrace())
        {
            $content .= $this->createSection()
            ->setTitle(t('Stack trace'))
            ->makeCollapsible(true)
            ->setContent('<pre>'.$entry->getTrace()->toString().'</pre>');
        }
        
        if($entry->hasApplog())
        {
            $content .= $this->createSection()
            ->setTitle(t('Application log'))
            ->makeCollapsible(true)
            ->setContent(
                '<pre style="background:#fff;font-family:monospace;font-size:14px;color:#444;padding:16px;border:solid 1px #999;border-radius:4px;">'.
                    print_r($entry->getApplog(), true).
                '</pre>'
            );
        }
        
        return $this->renderer
        ->setTitle(t(
            '%1$s log entry registered %2$s',
            $entry->getTypeLabel(),
            $entry->getTimePretty()
        ))
        ->appendContent($content)
        ->render();
    }
    
    protected function relativizePath($path)
    {
        $appFolder = str_replace('\\', '/', APP_ROOT);
        $path = str_replace('\\', '/', $path);

        return str_replace($appFolder, '', $path);
    }

    /**
     * @var UI_DataGrid
     */
    protected $datagrid;

    protected function createDatagrid()
    {
        $grid = $this->ui->createDataGrid('errorlog');
        $grid->addColumn('date', t('Date'))->setNowrap();
        $grid->addColumn('type', t('Type'));
        $grid->addColumn('code', t('Code'))->alignRight();
        $grid->addColumn('message', t('Message'));
        $grid->addColumn('referer', t('Source page'));
        $grid->addColumn('username', t('Username'));
        $grid->addColumn('log', t('Applog?'))->setNowrap()->alignCenter();
        
        $this->datagrid = $grid;
    }
}