<?php
/**
 * @package UserInterface
 * @subpackage ClientResources
 * @see \UI\ClientResourceCollection
 */

declare(strict_types=1);

namespace UI;

use UI;
use UI_ClientResource;
use UI_ClientResource_Javascript;
use UI_ClientResource_Stylesheet;
use UI_ResourceManager;

/**
 * Helper class that can be used to keep track of all client
 * resources that get added, and access the list afterwards.
 * Resources can be added just like the {@see UI} class.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class ClientResourceCollection
{
    private UI_ResourceManager $resourceManager;

    /**
     * @var array<string,UI_ClientResource>
     */
    private array $resources = array();

    /**
     * @param UI|UI_ResourceManager $uiOrResourceManager
     */
    public function __construct($uiOrResourceManager)
    {
        if($uiOrResourceManager instanceof UI) {
            $this->resourceManager = $uiOrResourceManager->getResourceManager();
        } else {
            $this->resourceManager = $uiOrResourceManager;
        }
    }

    public function getUI(): UI
    {
        return $this->resourceManager->getUI();
    }

    /**
     * @return UI_ClientResource[]
     */
    public function getResources() : array
    {
        return array_values($this->resources);
    }

    public function hasResources() : bool
    {
        return !empty($this->resources);
    }

    public function addJavascript(string $fileOrUrl, int $priority = 0, bool $defer=false) : UI_ClientResource_Javascript
    {
        $resource = $this->resourceManager->addJavascript($fileOrUrl, $priority, $defer);
        $this->addResource($resource);
        return $resource;
    }

    public function addVendorJavascript(string $packageName, string $file, int $priority=0) : UI_ClientResource_Javascript
    {
        $resource = $this->resourceManager->addVendorJavascript($packageName, $file, $priority);
        $this->addResource($resource);
        return $resource;
    }

    public function addVendorStylesheet(string $packageName, string $file, int $priority=0) : UI_ClientResource_Stylesheet
    {
        $resource =  $this->resourceManager->addVendorStylesheet($packageName, $file, $priority);
        $this->addResource($resource);
        return $resource;
    }

    public function addStylesheet(string $fileOrUrl, string $media = 'all', int $priority = 0) : UI_ClientResource_Stylesheet
    {
        $resource = $this->resourceManager->addStylesheet($fileOrUrl, $media, $priority);
        $this->addResource($resource);
        return $resource;
    }

    private function addResource(UI_ClientResource $resource) : void
    {
        $this->resources[$resource->getFileOrURL()] = $resource;
    }
}
