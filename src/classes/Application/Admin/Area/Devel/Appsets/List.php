<?php

require_once 'Application/Admin/Area/Mode/Submode.php';

class Application_Admin_Area_Devel_Appsets_List extends Application_Admin_Area_Mode_Submode
{
    public function getURLName()
    {
        return 'list';
    }
    
    public function getTitle()
    {
        return t('List of application sets');
    }
    
    public function getNavigationTitle()
    {
        return t('List');
    }
    
    public function getDefaultAction()
    {
        return null;
    }
    
    /**
     * @var Application_Sets
     */
    protected $sets;
    
    protected function _handleActions()
    {
        $this->sets = $this->driver->getApplicationSets();
        
        $this->createDataGrid();
    }
    
    protected function _handleSidebar()
    {
        $this->sidebar->addButton('addset', t('Add new set'))
        ->setIcon(UI::icon()->add())
        ->makePrimary()
        ->makeLinked($this->sets->getAdminCreateURL());
        
        $this->sidebar->addSeparator();
        
        $this->sidebar->addHelp(
            t('Using application sets'),
            '<p>'.t('An application set can be selected by adding the %1$s application configuration setting, and specifying the ID of the application set as its value.', '<code>APP_APPSET</code>').'</p>'.
            '<p>'.t('When no application set is specified, it is assumed all areas are enabled, and the default area is the first in the list.').'</p>'
        );
    }
    
    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromSubmode($this);
    }
    
    protected function _renderContent()
    {
        $entries = array();
        
        $sets = $this->sets->getSets();
        foreach($sets as $set) {
            $active = '';
            if($set->isActive()) {
                $active = UI::icon()->ok()->makeSuccess();
            } else {
                $active = UI::icon()->disabled()->makeMuted();
            }
            
            $entries[] = array(
                'id' => '<a href="'.$set->getAdminEditURL().'">'.$set->getID().'</a>',
                'active' => $active,
                'default' => $set->getDefaultArea()->getTitle(),
                'enabled' => implode(', ', $set->getEnabledAreaNames(false))
            );
        }
        
        return $this->renderDatagrid(
            $this->getTitle(),
            $this->dataGrid,
            $entries
        );
    }
    
    /**
     * @var UI_DataGrid
     */
    protected $dataGrid;
    
    protected function createDataGrid()
    {
        $grid = $this->ui->createDataGrid('appsets');
        $grid->addColumn('id', t('ID'))->setNowrap()->setCompact();
        $grid->addColumn('active', t('Current?'))->setCompact()->alignCenter();
        $grid->addColumn('default', t('Default area'));
        $grid->addColumn('enabled', t('Enabled areas'));
        
        $this->dataGrid = $grid;
    }
}