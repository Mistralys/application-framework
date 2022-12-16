<?php

use Application\Admin\Area\Devel\BaseDeploymentHistoryScreen;

abstract class Application_Admin_Area_Devel extends Application_Admin_Area
{
    public const URL_NAME = 'devel';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }
    
    public function getDefaultMode() : string
    {
        return 'overview';
    }
    
    public function getTitle() : string
    {
        return t('Developer tools');
    }
    
    public function getNavigationTitle() : string
    {
        return t('Developer tools');
    }
    
    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }
    
    public function getNavigationGroup() : string
    {
        return t('Manage');
    }
    
    public function getNavigationIcon() : ?UI_Icon
    {
        return UI::icon()->developer();
    }
    
    public function isCore() : bool
    {
        return true;
    }
    
    public function getDependencies() : array
    {
        return array();
    }
    
    abstract protected function initItems() : void;
    
    protected $items;
    
    public function getItems()
    {
        if(!isset($this->items)) {
            $this->items = array();
            $this->initItems();
        }
        
        return $this->items;
    }
    
    protected function registerItem(string $urlName, string $label, string $categoryLabel='')
    {
        if(empty($categoryLabel)) {
            $categoryLabel = t('Miscellaneous');
        }
        
        if(!isset($this->items[$categoryLabel])) {
            $this->items[$categoryLabel] = array();
        }
        
        $this->items[$categoryLabel][$urlName] = $label;
    }
 
    protected function registerMaintenance($category=null)
    {
        $this->registerCoreItem('maintenance', t('Maintenance'), t('Tools'), $category);
    }
    
    protected function registerAppInterface($category=null)
    {
        $this->registerCoreItem('appinterface', t('Interface refs'), t('References'), $category);
    }
    
    protected function registerAppSets($category=null)
    {
        $this->registerCoreItem('appsets', t('Appsets'), t('Settings'), $category);
    }
    
    protected function registerErrorLog($category=null)
    {
        $this->registerCoreItem('errorlog', t('Error logs'), t('Logs'), $category);
    }

    protected function registerWhatsNewEditor($category=null)
    {
        $this->registerCoreItem(
            Application_Admin_Area_Devel_WhatsNewEditor::URL_NAME,
            t('What\'s new editor'),
            t('Tools'),
            $category
        );
    }

    protected function registerDeploymentRegistry(?string $category=null) : void
    {
        if(!Application::isDatabaseEnabled()) {
            return;
        }

        $this->registerCoreItem(
            BaseDeploymentHistoryScreen::URL_NAME,
            t('Deployment history'),
            t('Tools'),
            $category
        );
    }
    
    protected function registerAppLogs(?string $category=null) : void
    {
        if(Application::isDatabaseEnabled()) {
            $this->registerCoreItem('messagelog', t('Message log'), t('Logs'), $category);
        }
    }

    protected function registerDBDumps($category=null)
    {
        if(Application::isDatabaseEnabled()) {
            $this->registerCoreItem('dbdump', t('Database dumps'), t('Tools'), $category);
        }
    }

    protected function registerAppSettings($category=null)
    {
        $this->registerCoreItem('appsettings', t('Application settings'), t('Settings'), $category);
    }

    protected function registerUsers($category=null)
    {
        $this->registerCoreItem('users', t('Users'), t('Tools'), $category);
    }

    protected function registerRightsOverview($category=null)
    {
        $this->registerCoreItem('rightsoverview', t('User rights overview'), t('Tools'), $category);
    }
    
   /**
    * Registers a core item, which always has a default category label.
    * This allows adding the item with a custom category, and otherwise
    * the default label is used.
    *
    * @param string $urlName
    * @param string $label
    * @param string $defaultCategory
    * @param string|NULL $categoryLabel
    */
    protected function registerCoreItem(string $urlName, string $label, string $defaultCategory, ?string $categoryLabel=null)
    {
        if(empty($categoryLabel)) {
            $categoryLabel = $defaultCategory;
        }
        
        $this->registerItem($urlName, $label, $categoryLabel);
    }
    protected function _handleSubnavigation() : void
    {
        $this->injectSubnavigation($this->subnav);
    }
    
    public function injectSubnavigation(UI_Page_Navigation $subnav)
    {
        $items = array_merge(
            array(
                'overview' => t('Overview')
            ),
            $this->getItems()
        );
        
        foreach($items as $mode => $title) {
            if(is_array($title)) {
                foreach($title as $mmode => $ttitle) {
                    $subnav->addInternalLink(self::URL_NAME, $ttitle, array('mode' => $mmode))->setGroup($mode);
                }
                
                continue;
            }
            
            $subnav->addInternalLink(self::URL_NAME, $title, array('mode' => $mode));
        }
    }
}