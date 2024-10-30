<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\OfflineEvents\RegisterCacheLocationsEvent;

use Application\AppFactory\ClassCacheHandler;
use Application\OfflineEvents\RegisterCacheLocationsEvent;
use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\NamedClosure;
use Closure;

/**
 * @package Application
 * @subpackage CacheControl
 *
 * @see ClassCacheHandler::getCacheLocation()
 */
class RegisterClassCacheHandler extends Application_EventHandler_OfflineEvents_OfflineListener
{
    protected function wakeUp(): NamedClosure
    {
        $callback = array($this, 'handleEvent');

        return NamedClosure::fromClosure(Closure::fromCallable($callback), $callback);
    }

    private function handleEvent(RegisterCacheLocationsEvent $event): void
    {
        $event->registerLocation(ClassCacheHandler::getCacheLocation());
    }
}
