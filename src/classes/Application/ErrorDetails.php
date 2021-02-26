<?php

class Application_ErrorDetails
{
    protected $title;
    
    protected $abstract;
    
    protected $themePath;
    
    protected $themeURL;
    
    protected $sentContent;
    
    protected $exception;
    
    protected $develinfo;
    
   /**
    * @var string[]
    */
    protected $themeLocations;
    
   /**
    * @var string
    */
    protected $contentType;
    
    public function __construct($title, $abstract, $themePath, $themeURL, $themeLocations, $sentContent, $contentType, Exception $e, $develinfo=false)
    {
        $this->title = $title;
        $this->abstract = $abstract;
        $this->themePath = $themePath;
        $this->themeURL = $themeURL;
        $this->sentContent = $sentContent;
        $this->exception = $e;
        $this->develinfo = $develinfo;
        $this->contentType = $contentType;
        $this->themeLocations = $themeLocations;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getAbstract()
    {
        return $this->abstract;
    }
    
    public function getThemePath()
    {
        return $this->themePath;
    }
    
    public function getThemeURL()
    {
        return $this->themeURL;
    }
    
    public function getSentContent()
    {
        return $this->sentContent;
    }
    
    public function getException()
    {
        return $this->exception;
    }
    
    public function isDeveloperInfoEnabled()
    {
        return $this->develinfo;
    }
    
    public function renderException()
    {
        return renderExceptionInfo($this->getException(), $this->isDeveloperInfoEnabled(), $this->isHTML());
    }

    public function renderPreviousException()
    {
        $prev = $this->getException()->getPrevious();
        if($prev) 
        {
            return renderExceptionInfo($prev, $this->isDeveloperInfoEnabled(), $this->isHTML(), false).
            '<h4 class="errorpage-header">Stack trace</h4>'.
            renderTrace($prev);
        }
        
        return '';
    }
    
    public function renderTrace()
    {
        return renderTrace($this->getException());
    }
    
    public function isHTML()
    {
        return $this->contentType == 'html';
    }
    
    public function hasPreviousException()
    {
        return $this->getException()->getPrevious();
    }
    
   /**
    * Attempts to find a file in any of the available theme locations:
    * This searches in the selected theme first -if any- and then in
    * the default theme.
    * 
    * @param string $file The relative path to the file, e.g. "img/logo-big.png"
    * @return Application_ErrorDetails_ThemeFile|NULL
    */
    public function findFile($file)
    {
        foreach($this->themeLocations as $location) {
            $path = $location[1].'/'.$file;
            if(file_exists($path)) {
                return new Application_ErrorDetails_ThemeFile(
                    $path,
                    $location[0].'/'.$file
                );
            }
        }
        
        return null;
    }
}

class Application_ErrorDetails_ThemeFile
{
    protected $path;
    
    protected $url;
    
    public function __construct($path, $url)
    {
        $this->path = $path;
        $this->url = $url;
    }
    
    public function getURL()
    {
        return $this->url;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
}
