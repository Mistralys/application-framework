<?php

declare(strict_types=1);

/**
 * @property Application_Admin_Area_Devel $area
 */
class Application_Admin_Area_Devel_Overview extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'overview';

    public function getURLName() : string
    {
        return self::URL_NAME;
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
    
    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }
    
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