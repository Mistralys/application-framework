<?php

abstract class UI_Themes_Theme
{
    const ERROR_RESOURCE_FILE_NOT_FOUND = 27401;

    const LOCATION_DRIVER = 'driver';
    
    const LOCATION_DEFAULT = 'default';
    
    const FILE_TYPE_STYLESHEET = 'css';
    
    const FILE_TYPE_JAVASCRIPT = 'js';
    
    const FILE_TYPE_TEMPLATE = 'templates';
    
    const FILE_TYPE_GRAPHIC = 'img';
    
   /**
    * @var string
    */
    protected $id;
    
   /**
    * @var UI_Themes
    */
    protected $themes;
    
   /**
    * @var string[]
    */
    protected $paths;
    
   /**
    * @var string[]
    */
    protected $urls;
    
    protected $requiredFiles = array(
        'img/ajax-error.png',
        'img/logo_big.png',
        'img/empty-image.png',
        'img/avatar-dummyuser.png',
        'img/empty-image.png',
        'img/logo-navigation-standalone.png', // if the appswitcher is disabled
        'img/logo-navigation.png', // if the appswitcher is enabled
        'img/logo-navigation-over.png' // if the appswitcher is enabled
    );
    
   /**
    * @var UI
    */
    protected $ui;
    
    public function __construct(UI_Themes $themes, $themeID)
    {
        $this->id = $themeID;
        $this->themes = $themes;
        $this->ui = $themes->getUI();
        
        $this->paths = array(
            self::LOCATION_DRIVER => APP_ROOT.'/themes/'.$themeID,
            self::LOCATION_DEFAULT => APP_INSTALL_FOLDER.'/themes/'.$themeID
        );
        
        $this->urls = array(
            self::LOCATION_DRIVER => APP_URL.'/themes/'.$themeID,
            self::LOCATION_DEFAULT => APP_INSTALL_URL.'/themes/'.$themeID
        );
    }
    
    public function getID()
    {
        return $this->id;
    }
    
   /**
    * Retrieves the base paths to all resource
    * repositories. 
    * 
    * @return array
    */
    public function getResourcePaths() : array
    {
        return $this->paths;
    }
    
    public function getDefaultPath()
    {
        return $this->paths[self::LOCATION_DEFAULT];
    }
    
    public function getDefaultURL()
    {
        return $this->urls[self::LOCATION_DEFAULT];
    }
    
    public function getDriverPath()
    {
        return $this->paths[self::LOCATION_DRIVER];
    }
    
    public function getDriverURL()
    {
        return $this->urls[self::LOCATION_DRIVER];
    }
    
    public function getDefaultTemplatesPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_TEMPLATE);
    }
    
    public function getDefaultTemplatesURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_TEMPLATE);
    }
    
    public function getDefaultImagesPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_GRAPHIC);
    }
    
    public function getDefaultImagesURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_GRAPHIC);
    }
    
    public function getDefaultStylesheetsURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_STYLESHEET);
    }
    
    public function getDefaultStylesheetsPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_STYLESHEET);
    }

    public function getDefaultJavascriptsURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_JAVASCRIPT);
    }
    
    public function getDefaultJavascriptsPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_JAVASCRIPT);
    }

    public function getDriverTemplatesPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_TEMPLATE, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverTemplatesURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_TEMPLATE, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverImagesPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_GRAPHIC, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverImagesURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_GRAPHIC, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverStylesheetsURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_STYLESHEET, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverStylesheetsPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_STYLESHEET, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverJavascriptsURL()
    {
        return $this->getResourceURL(self::FILE_TYPE_JAVASCRIPT, null, self::LOCATION_DRIVER);
    }
    
    public function getDriverJavascriptsPath()
    {
        return $this->getResourcePath(self::FILE_TYPE_JAVASCRIPT, null, self::LOCATION_DRIVER);
    }
    
    public function getImageURL($fileName)
    {
        return $this->getResourceURL(self::FILE_TYPE_GRAPHIC, $fileName);
    }
    
    public function getImagePath($fileName)
    {
        return $this->getResourcePath(self::FILE_TYPE_GRAPHIC, $fileName);
    }
    
    public function getStylesheetURL($fileName)
    {
        return $this->getResourceURL(self::FILE_TYPE_STYLESHEET, $fileName);
    }
    
    public function getStylesheetPath($fileName)
    {
        return $this->getResourcePath(self::FILE_TYPE_STYLESHEET, $fileName);
    }

    public function getJavascriptURL($fileName)
    {
        return $this->getResourceURL(self::FILE_TYPE_JAVASCRIPT, $fileName);
    }
    
    public function getJavascriptPath($fileName)
    {
        return $this->getResourcePath(self::FILE_TYPE_JAVASCRIPT, $fileName);
    }

    public function getTemplateURL($fileName)
    {
        return $this->getResourceURL(self::FILE_TYPE_TEMPLATE, $fileName);
    }
    
    public function getTemplatePath($fileName)
    {
        return $this->getResourcePath(self::FILE_TYPE_TEMPLATE, $fileName);
    }
    
    public function getResourceURL($type, $fileName=null, $location=self::LOCATION_DEFAULT)
    {
        return $this->getResource($this->urls, $type, $fileName, $location);
    }
    
    public function getResourcePath($type, $fileName=null, $location=self::LOCATION_DEFAULT)
    {
        return $this->getResource($this->paths, $type, $fileName, $location);
    }
    
    protected function getResource($sources, $type, $fileName=null, $location=self::LOCATION_DEFAULT)
    {
        if(empty($fileName)) {
            return $sources[$location].'/'.$type;
        }
        
        $location = $this->findResource($fileName, $type);
        if($location) {
            return $sources[$location].'/'.$type.'/'.$fileName;
        }
        
        throw new Application_Exception(
            sprintf(
                'Cannot find a required resource in %s '.$fileName,
                $type
            ),
            sprintf(
                'The resource file [%s] of type [%s] could not be found in the theme folder, nor in the driver\'s theme folder.',
                $fileName,
                $type
            ),
            self::ERROR_RESOURCE_FILE_NOT_FOUND
        );
    }
    
    public function injectJS()
    {
        $this->ui->addJavascript('ui/theme.js');
        
        $this->ui->addJavascriptHeadStatement(
            'UI.SetTheme', 
            $this->getID(), 
            $this->getDefaultURL(),
            $this->getDriverURL()
        );
    }
    
    public function findResource($fileName, $resourceType)
    {
        foreach($this->paths as $location => $path) 
        {
            $filePath = $path.'/'.$resourceType.'/'.$fileName;
            
            if(file_exists($filePath)) {
                return $location;
            }
        }
        
        return null;
    }
    
    public function injectDependencies()
    {
        $this->injectJS();
        
        $this->_injectDependencies();   
    }
    
    abstract protected function _injectDependencies();
    
   /**
    * Creates a new content renderer instance.
    * 
    * @param UI $ui
    * @return UI_Themes_Theme_ContentRenderer
    */
    public function createContentRenderer(UI $ui) : UI_Themes_Theme_ContentRenderer
    {
        return new UI_Themes_Theme_ContentRenderer($ui);
    }
}
