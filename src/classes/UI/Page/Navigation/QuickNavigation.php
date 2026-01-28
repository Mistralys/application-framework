<?php

declare(strict_types=1);

namespace UI\Page\Navigation;

use Application\Admin\Area\Events\UIHandlingCompleteEvent;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Driver;
use Application\EventHandler\EventManager;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\NamedClosure;
use Closure;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI\Page\Navigation\QuickNavigation\Items\ScreenNavItem;
use UI\Page\Navigation\QuickNavigation\Items\URLNavItem;
use UI\Page\Navigation\QuickNavigation\ScreenItemsContainer;
use UI_Exception;
use UI_Page_Header;
use UI_Page_Navigation;
use UI_Renderable_Interface;

class QuickNavigation implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const int ERROR_NO_ACTIVE_SCREEN_SET = 110701;
    public const int ERROR_NO_CONTAINER_FOR_SCREEN = 110702;

    public const string NAV_AREA_QUICK_NAVIGATION = 'area-quick-nav';

    private ?UI_Page_Navigation $navigation = null;
    private ?AdminScreenInterface $workScreen = null;
    private UI_Page_Header $header;

    /**
     * @var array<string,ScreenItemsContainer>
     */
    private array $containers = array();

    public function __construct(UI_Page_Header $header)
    {
        $this->header = $header;
        $this->navigation = $this->header
            ->getPage()
            ->createNavigation(self::NAV_AREA_QUICK_NAVIGATION);

        EventManager::addListener(
            UIHandlingCompleteEvent::EVENT_NAME,
            NamedClosure::fromClosure(
                Closure::fromCallable(array($this, 'event_areaUIHandlingComplete')),
                array($this, 'event_areaUIHandlingComplete')
            )
        );
    }

    public function getUI() : UI
    {
        return $this->header->getUI();
    }

    private function event_areaUIHandlingComplete(UIHandlingCompleteEvent $event) : void
    {
        if(!$this->hasItems()) {
            $this->logUI('No quick navigation items present, ignoring.');
            return;
        }

        $this->logUI('Quick navigation items present, adding them.');

        $this->configureNavigation();

        $this->header->addNavigationInstance($this->navigation);
    }

    private function configureNavigation() : void
    {
        $containers = $this->resolveContainers();

        foreach($containers as $container)
        {
            $container->injectElements($this->navigation);
        }
    }

    public function hasItems() : bool
    {
        $containers = $this->resolveContainers();

        foreach($containers as $container)
        {
            if($container->hasItems())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets all screen item containers that must be
     * rendered in the active admin screen. This is
     * all of them, except when the active screen
     * requested the exclusivity - then only the
     * active screen's items container is used.
     *
     * @return ScreenItemsContainer[]
     */
    public function resolveContainers() : array
    {
         $active = $this->getActiveContainer();

         if($active !== null && $active->isExclusive()) {
             return array($active);
         }

         return array_values($this->containers);
    }

    public function getActiveContainer() : ?ScreenItemsContainer
    {
        $screen = Application_Driver::getInstance()->getActiveScreen();

        if($screen !== null) {
            return $this->getContainerByScreen($screen);
        }

        return null;
    }

    protected function getContainerByScreen(AdminScreenInterface $screen) : ?ScreenItemsContainer
    {
        $path = $screen->getURLPath();

        if(isset($this->containers[$path])) {
            return $this->containers[$path];
        }

        return null;
    }

    public function setWorkScreen(AdminScreenInterface $screen) : void
    {
        $this->workScreen = $screen;
    }

     private function getWorkScreen() : AdminScreenInterface
     {
         if(isset($this->workScreen)) {
             return $this->workScreen;
         }

         throw new UI_Exception(
             'No active screen has been set.',
             'The quick navigation requires an active screen to be set when adding items.',
             self::ERROR_NO_ACTIVE_SCREEN_SET
         );
     }

     public function makeExclusive() : self
     {
         $this->getWorkContainer()->makeExclusive();
         return $this;
     }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string|AdminURLInterface $url
     * @return URLNavItem
     * @throws UI_Exception
     */
     public function addURL($label, $url) : URLNavItem
     {
         return $this->getWorkContainer()->addURL($label, $url);
     }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param array $params
     * @return ScreenNavItem
     * @throws UI_Exception
     */
     public function addScreen($label, array $params=array()) : ScreenNavItem
     {
         return $this->getWorkContainer()->addScreen($label, $params);
     }

     private function getWorkContainer() : ScreenItemsContainer
     {
         $screen = $this->getWorkScreen();
         $path = $screen->getURLPath();

         if(isset($this->containers[$path]))
         {
             return $this->containers[$path];
         }

         $container = new ScreenItemsContainer($this);

         $this->containers[$path] = $container;

         return $container;
     }

    public function getLogIdentifier() : string
    {
        return 'QuickNavigation';
    }
}
