<?php
/**
 * File containing the {@link UI_ClientResource} class.
 * @package UserInterface
 * @subpackage ClientResources
 * @see UI_ClientResource
 */

declare(strict_types=1);

use AppUtils\FileHelper;
use function AppUtils\parseURL;

/**
 * Abstract base class for clientside resource files.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * 
 * @see UI_ResourceManager
 * @see UI_ClientResource_Javascript
 * @see UI_ClientResource_Stylesheet
 */
abstract class UI_ClientResource
{
   /**
    * @var UI
    */
    private $ui;
    
   /**
    * @var string
    */
    private $fileOrURL;
    
   /**
    * @var int
    */
    private $key;
    
   /**
    * @var integer
    */
    private $priority = 0;
    
   /**
    * @var string
    */
    private $fileType;

   /**
    * @var UI_Themes_Theme
    */
    private $theme;
    
   /**
    * @var boolean
    */
    private $enabled = true;
    
    public function __construct(UI $ui, string $fileOrURL, int $loadKey)
    {
        $this->ui = $ui;
        $this->fileOrURL = $fileOrURL;
        $this->key = $loadKey;
        $this->fileType = $this->_getFileType();
        $this->theme = $ui->getTheme();

        $this->init();
    }

    abstract protected function init() : void;
    
    abstract protected function _getFileType() : string;
    
    public function getFileOrURL() : string
    {
        return $this->fileOrURL;
    }
    
    public function getKey() : int
    {
        return $this->key;
    }
    
    public function disable() : UI_ClientResource
    {
        $this->enabled = false;
        
        return $this;
    }
    
    public function isEnabled() : bool
    {
        return $this->enabled && !$this->isAvoidable();
    }
    
   /**
    * Whether this resource has already been loaded in the current
    * request, and does not have to be included again.
    * 
    * @return bool
    */
    public function isAvoidable() : bool
    {
        $loaded = $this->ui->getResourceManager()->getLoadedResourceKeys();
        
        return in_array($this->key, $loaded);
    }
    
   /**
    * Whether the resource has been specified with an absolute
    * `http` URL, meaning it is an external resource (like jQuery).
    * 
    * @return bool
    */
    public function isAbsoluteURL() : bool
    {
        $start = strtolower(substr($this->fileOrURL, 0, 4));
        
        return $start === 'http';
    }
    
   /**
    * File type identifier, e.g. "js".
    * 
    * @return string
    * 
    * @see UI_Themes_Theme::FILE_TYPE_STYLESHEET
    * @see UI_Themes_Theme::FILE_TYPE_JAVASCRIPT
    * @see UI_Themes_Theme::FILE_TYPE_TEMPLATE
    * @see UI_Themes_Theme::FILE_TYPE_GRAPHIC 
    */
    public function getFileType() : string
    {
        return $this->fileType;
    }
    
   /**
    * Sets the priority with which it should be included
    * in the page.
    * 
    * @param int $priority
    * @return UI_ClientResource
    */
    public function setPriority(int $priority) : UI_ClientResource
    {
        $this->priority = $priority;
        return $this;
    }
    
    public function getPriority() : int
    {
        return $this->priority;
    }
    
   /**
    * Retrieves the URL to include this resource.
    * 
    * Automatically returns the minified version
    * if enabled, and includes the application's
    * build key parameter if present.
    * 
    * @return string
    */
    public function getURL() : string
    {
        if($this->isAbsoluteURL())
        {
            return $this->fileOrURL;
        }
        
        $url = $this->theme->getResourceURL(
            $this->fileType, 
            $this->getMinifiedFileName()
        );
        
        if($this->ui->hasBuildKey())
        {
            $url = parseURL($url)
            ->setParam('_buildkey', $this->ui->getBuildKey())
            ->getNormalized();
        }
            
        return $url;
    }
    
   /**
    * If we want to use the minified versions of scripts,
    * this will check if there is a file with the same name,
    * but with `-min` appended. This is then used instead.
    *
    * Note: this will not be applied to absolute URLs.
    *
    * @return string
    */
    public function getMinifiedFileName() : string
    {
        if(!UI::isJavascriptMinified() || $this->isAbsoluteURL())
        {
            return $this->fileOrURL;
        }
        
        $fileName = $this->fileOrURL;
        
        $path = $this->getPath();
        
        $basename = basename($fileName);
        $ext = pathinfo($basename, PATHINFO_EXTENSION);
        $newPath = str_replace('.' . $ext, '-min.' . $ext, $path);
        
        if (file_exists($newPath))
        {
            $fileName = str_replace('.' . $ext, '-min.' . $ext, $fileName);
        }
        
        return $fileName;
    }
    
    public function getPath() : string
    {
        if($this->isAbsoluteURL())
        {
            $info = parseURL($this->getURL());
            
            return $info->getHost().'/'.$info->getPath();
        }
        
        return $this->theme->getResourcePath(
            $this->fileType, 
            $this->fileOrURL
        );
    }
    
   /**
    * Relative path to the resource file.
    * 
    * @return string
    */
    public function getRelativePath() : string
    {
        $path = FileHelper::normalizePath($this->getPath());
        
        if($this->isAbsoluteURL())
        {
            return $path;
        }
        
        $paths = $this->theme->getResourcePaths();
        foreach($paths as $themePath)
        {
            $themePath = FileHelper::normalizePath($themePath);
            
            $path = str_replace($themePath, '', $path);
        }
        
        return ltrim($path, '/');
    }
    
    public function toArray() : array
    {
        return array(
            'relative' => $this->getRelativePath(),
            'url' => $this->getURL(),
            'key' => $this->getKey(),
            'type' => $this->getFileType()
        );
    }
}
