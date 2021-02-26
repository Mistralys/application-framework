<?php

    /* @var $this UI_Page_Template */

    $configFile = $this->driver->getConfigFolder().'/appswitcher.php';
    if(!file_exists($configFile)) {
        echo 
        '<ul class="nav navbar-nav">'.
            '<li>'.
                '<a href="'.APP_URL.'" class="brand" title="'.$this->driver->getAppNameShort().'">'.
                    '<img src="'.imageURL('logo-navigation-standalone.png').'" alt=""/>'.
                '</a>'.
            '</li>'.
        '</ul>';
        
        return;
    }
    
    $apps = null;

    require_once $configFile;
    
    if(empty($apps) || !is_array($apps)) {
        echo $this->renderErrorMessage(t('No apps defined in the configuration file.'));
        return;
    }
    
    $html = 
    '<ul class="nav navbar-nav navbar-appswitcher">'.
        '<li class="dropdown">'.
            '<a href="'.APP_URL.'" data-toggle="dropdown" id="applauncher" class="brand" title="'.$this->driver->getAppNameShort().'">'.
                '<img src="'.imageURL('logo-navigation.png').'" alt="" onmouseover="this.src=\''.imageURL('logo-navigation-over.png').'\'" onmouseout="this.src=\''.imageURL('logo-navigation.png').'\'"/>'.
            '</a>'.
            '<ul class="dropdown-menu" role="menu" aria-labelledby="applauncher">';
                foreach($apps as $appDef) {
                    $html .=
                    '<li>'.
                        '<a href="'.$appDef['url'].'" title="'.$appDef['longName'].'">'.
                            $appDef['shortName'].
                        '</a>'.
                    '</li>';
                }
                $html .=
           '</ul>'.
        '</li>'.
    '</ul>';
    
    echo $html;