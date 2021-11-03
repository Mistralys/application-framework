<?php

abstract class Application_Admin_Area_Mode_Submode_Action_RevisionableStatus extends Application_Admin_Area_Mode_Submode_Action_Revisionable
{
    public function getURLName() : string
    {
        return 'status';
    }
    
    public function getTitle() : string
    {
        if(!$this->isAdminMode()) {
            return t('Status');
        }
        
        return t('%1$s status', $this->revisionable->getLabel());
    }
    
    public function getNavigationTitle() : string
    {
        return t('Status');
    }
    
    protected function _renderContent()
    {
        $table = $this->ui->createPropertiesGrid($this->collection->getRecordTypeName().'_status')->makeSection();
        
        $this->injectProperties($table);
        
        $table->injectRevisionDetails($this->revisionable, $this->revisionable->getAdminChangelogURL());
        
        return $this->renderContentWithSidebar(
            $table->render(),
            $this->revisionable->renderTitle()
        );
    }
    
    abstract protected function injectProperties(UI_PropertiesGrid $grid);
}