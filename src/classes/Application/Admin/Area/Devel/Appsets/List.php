<?php

declare(strict_types=1);

use Application\AppFactory;

class Application_Admin_Area_Devel_Appsets_List extends Application_Admin_Area_Mode_Submode
{
    public function getURLName() : string
    {
        return 'list';
    }
    
    public function getTitle() : string
    {
        return t('List of application sets');
    }
    
    public function getNavigationTitle() : string
    {
        return t('List');
    }
    
    public function getDefaultAction() : string
    {
        return '';
    }
    
    protected Application_Sets $sets;
    
    protected function _handleActions() : bool
    {
        $this->sets = AppFactory::createAppSets();
        
        $this->createDataGrid();

        return true;
    }
    
    protected function _handleSidebar() : void
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
    
    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromSubmode($this);
    }
    
    protected function _renderContent()
    {
        $entries = array();
        
        $sets = $this->sets->getSets();
        foreach($sets as $set)
        {
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
    
    protected UI_DataGrid $dataGrid;
    
    protected function createDataGrid() : void
    {
        $grid = $this->ui->createDataGrid('appsets');
        $grid->addColumn('id', t('ID'))->setNowrap()->setCompact();
        $grid->addColumn('active', t('Current?'))->setCompact()->alignCenter();
        $grid->addColumn('default', t('Default area'));
        $grid->addColumn('enabled', t('Enabled areas'));
        
        $this->dataGrid = $grid;
    }
}
