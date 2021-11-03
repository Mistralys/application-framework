<?php

class Application_Admin_Area_Devel_Overview extends Application_Admin_Area_Mode
{
    public function getURLName() : string
    {
        return 'overview';
    }
    
    public function getTitle() : string
    {
        return t('Developer tools overview');
    }
    
    public function getNavigationTitle() : string
    {
        return t('Overview');
    }
    
    public function getDefaultSubmode() : string
    {
        return '';
    }
    
    public function isUserAllowed() : bool
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