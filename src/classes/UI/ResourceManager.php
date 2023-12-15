<?php
/**
 * File containing the {@link UI_ResourceManager} class.
 * @package UserInterface
 * @subpackage ClientResources
 * @see UI_ResourceManager
 */

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\FileHelper;

/**
 * Handles the loading of clientside resources.
 * 
 * Each stylesheet or javascript include file gets a 
 * unique load key, which is automatically registered
 * clientside via the `application.registerLoadKey()`
 * method. This works also with dynamically loaded
 * contents.
 * 
 * These load keys are then used to determine which
 * of the requested includes actually have to be
 * included in the current page. They are submitted 
 * in every AJAX request via the `_loadkeys` parameter. 
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_ResourceManager
{
    public const ERROR_INVALID_RESOURCE_TYPE_PRIORITY = 62201;
    public const ERROR_UNKNOWN_RESOURCE_EXTENSION = 62202; 

    const LOADKEYS_REQUEST_VARIABLE = '_loadkeys';
    
   /**
    * @var UI
    */
    private $ui;
    
   /**
    * @var array<string,UI_ClientResource>
    */
    private $resources = array();

    /**
     * @var integer[]
     */
    private $loadedResourceKeys = array();
    
   /**
    * @var boolean
    */
    private $loadkeysLoaded = false;
    
    public function __construct(UI $ui)
    {
        $this->ui = $ui;
    }

    public function getUI() : UI
    {
        return $this->ui;
    }
    
    public function addJavascript(string $fileOrURL, int $priority = 0, bool $defer=false) : UI_ClientResource_Javascript
    {
        $loadKey = $this->registerClientResource($fileOrURL);
        
        $resource = new UI_ClientResource_Javascript($this->ui, $fileOrURL, $loadKey);
        $resource->setDefer($defer);
        
        return ClassHelper::requireObjectInstanceOf(
            UI_ClientResource_Javascript::class,
            $this->registerResource($resource, $priority)
        );
    }
    
    public function addStylesheet(string $fileOrURL, string $media = 'all', int $priority = 0) : UI_ClientResource_Stylesheet
    {
        $loadKey = $this->registerClientResource($fileOrURL);
        
        $resource = new UI_ClientResource_Stylesheet($this->ui, $fileOrURL, $loadKey);
        $resource->setMedia($media);
        
        return ClassHelper::requireObjectInstanceOf(
            UI_ClientResource_Stylesheet::class,
            $this->registerResource($resource, $priority)
        );
    }
    
    public function addResource(string $fileOrURL) : UI_ClientResource
    {
        $extension = FileHelper::getExtension($fileOrURL);
        
        switch($extension)
        {
            case 'js':
                return $this->addJavascript($fileOrURL);
                
            case 'css':
                return $this->addStylesheet($fileOrURL);
        }
        
        throw new Application_Exception(
            'Unknown resource file type.',
            sprintf(
                'The resource file extension [%s] does not match known file types.',
                $extension
            ),
            self::ERROR_UNKNOWN_RESOURCE_EXTENSION
        );
    }

    public function getVendorURL() : string
    {
        if(defined('APP_VENDOR_URL'))
        {
            return APP_VENDOR_URL;
        }

        return APP_URL.'/vendor';
    }

    public function addVendorJavascript(string $packageName, string $file, int $priority=0) : UI_ClientResource_Javascript
    {
        $url = $this->getVendorURL().'/'.$packageName.'/'.$file;
        
        return $this->addJavascript($url, $priority);
    }
    
    public function addVendorStylesheet(string $packageName, string $file, int $priority=0) : UI_ClientResource_Stylesheet
    {
        $url = $this->getVendorURL().'/'.$packageName.'/'.$file;
        
        return $this->addStylesheet($url, 'all', $priority);
    }
    
    private function registerResource(UI_ClientResource $resource, int $priority=0) : UI_ClientResource
    {
        $fileOrURL = $resource->getFileOrURL();
        
        if(isset($this->resources[$fileOrURL]))
        {
            return $this->resources[$fileOrURL];
        }
        
        $priority = $this->resolvePriority($resource->getFileType(), $priority);
        $resource->setPriority($priority);
        
        $this->resources[$fileOrURL] = $resource;
        
        return $resource;
    }
    
    /**
     * @var array<string,int>
     */
    private $priorities = array(
        UI_Themes_Theme::FILE_TYPE_JAVASCRIPT => 3000,
        UI_Themes_Theme::FILE_TYPE_STYLESHEET => 3000
    );
    
    private function resolvePriority(string $fileType, int $priority=0) : int
    {
        if($priority !== 0)
        {
            return $priority;
        }
        
        if(!isset($this->priorities[$fileType]))
        {
            throw new Application_Exception(
                'Unhandled resource type priority',
                sprintf(
                    'The resource type [%s] has no priority counter set.',
                    $fileType
                ),
                self::ERROR_INVALID_RESOURCE_TYPE_PRIORITY
            );
        }
        
        $this->priorities[$fileType]--;
        
        return $this->priorities[$fileType];
    }
    
   /**
    * Retrieves the unique load key that identifies javascript
    * or stylesheet includes.
    *
    * @param string $fileOrUrl The relative path to the file, e.g. <code>file.js</code> or <code>file.css</code>
    * @return integer
    */
    public function registerClientResource(string $fileOrUrl) : int
    {
        $this->loadKeys();

        if(isset($this->keys['scripts'][$fileOrUrl]))
        {
            return $this->keys['scripts'][$fileOrUrl];
        }
        
        $this->keys['counter']++;
        $id = $this->keys['counter'];

        $this->keys['scripts'][$fileOrUrl] = $id;

        Application_Driver::createSettings()->setArray('client-keys', $this->keys);

        return $id;
    }

    private bool $keysLoaded = false;

    /**
     * @var array{counter:int,scripts:array<string,int>}
     */
    private array $keys = array(
        'counter' => 0,
        'scripts' => array()
    );

    private function loadKeys() : void
    {
        if($this->keysLoaded === true)
        {
            return;
        }

        $keys = Application_Driver::createSettings()->getArray('client-keys');

        if(!empty($keys))
        {
            $this->keys = $keys;
        }

        $this->keysLoaded = true;
    }

   /**
    * Returns an indexed array with client resource keys
    * that have been specified as already loaded in the
    * request, using the <code>_loadkeys</code> parameter.
    * This parameter is set automatically by an AJAX calls
    * in the application, in order to avoid loading resources
    * multiple times.
    *
    * @return integer[]
    */
    public function getLoadedResourceKeys() : array
    {
        if($this->loadkeysLoaded) 
        {
            return $this->loadedResourceKeys;
        }
        
        $driver = Application_Driver::getInstance();
        $request = $driver->getRequest();
        $ids = $request->registerParam(self::LOADKEYS_REQUEST_VARIABLE)->setIDList()->get();
        
        $this->loadedResourceKeys = $ids;
        $this->loadkeysLoaded = true;
        
        return $this->loadedResourceKeys;
    }

   /**
    * Clears all script load keys present in the current request,
    * if any. Use this if you do not wish to avoid loading stylesheets
    * and javascripts when using AJAX calls.
    */
    public function clearLoadkeys()
    {
        if(isset($_REQUEST[self::LOADKEYS_REQUEST_VARIABLE])) {
            unset($_REQUEST[self::LOADKEYS_REQUEST_VARIABLE]);
        }
        
        $this->loadedResourceKeys = array();
        $this->loadkeysLoaded = false;
    }
    
   /**
    * @return UI_ClientResource_Javascript[]
    */
    public function getJavascripts() : array
    {
        $result = array();
        
        foreach($this->resources as $resource)
        {
            if(!$resource->isEnabled())
            {
                continue;
            }
            
            if($resource instanceof UI_ClientResource_Javascript)
            {
                $result[] = $resource;
            }
        }
        
        $this->sortByPriority($result);
        
        return $result;
    }
    
   /**
    * @return UI_ClientResource_Stylesheet[]
    */
    public function getStylesheets() : array
    {
        $result = array();
        
        foreach($this->resources as $resource)
        {
            if(!$resource->isEnabled())
            {
                continue;
            }
            
            if($resource instanceof UI_ClientResource_Stylesheet)
            {
                $result[] = $resource;
            }
        }
        
        $this->sortByPriority($result);
        
        return $result;
    }
    
    private function sortByPriority(array &$resources) : void
    {
        usort($resources, function(UI_ClientResource $a, UI_ClientResource $b) 
        {
            if ($a->getPriority() > $b->getPriority()) {
                return -1;
            }
            
            if ($a->getPriority() < $b->getPriority()) {
                return 1;
            }
            
            return 0;
        });
    }
    
    public function renderIncludes() : string
    {
        $html = '';
        
        $stylesheets = $this->getStylesheets();
        
        foreach($stylesheets as $sheet) 
        {
            $this->registerClientside($sheet);
            
            $html .= $sheet->renderTag().PHP_EOL;
        }
        
        $javascripts = $this->getJavascripts();
        
        foreach($javascripts as $js)
        {
            $this->registerClientside($js);
            
            $html .= $js->renderTag().PHP_EOL;
        }
        
        return $html;
    }
    
    private function registerClientside(UI_ClientResource $resource)
    {
        $this->ui->addJavascriptOnloadStatement(
            'application.registerLoadKey',
            $resource->getKey(),
            $resource->getRelativePath()
        );
    }
}
