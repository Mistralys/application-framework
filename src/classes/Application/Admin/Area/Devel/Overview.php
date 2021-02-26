<?php

class Application_Admin_Area_Devel_Overview extends Application_Admin_Area_Mode
{
    public function getURLName()
    {
        return 'overview';
    }
    
    public function getTitle()
    {
        return t('Developer tools overview');
    }
    
    public function getNavigationTitle()
    {
        return t('Overview');
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
    
    /**
     * @var Application_Admin_Area_Devel
     */
    protected $area;
    
    protected function _renderContent()
    {
        return $this->renderContentWithoutSidebar(
            $this->renderTemplate(
                'devel.overview',
                array(
                    'items' => $this->area->getItems()
                )
            ),
            $this->getTitle()
        );
    }
}