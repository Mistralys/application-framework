<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\SessionInstantiatedEvent;

use Application\OfflineEvents\SessionInstantiatedEvent;
use Application_EventHandler_OfflineEvents_OfflineListener;
use AppUtils\NamedClosure;
use Closure;

class TestSessionInstantiatedListener extends Application_EventHandler_OfflineEvents_OfflineListener
{
    public const CONSTANT_INSTANTIATED = 'SESSION_INSTANTIATED_EVENT_CALLED';
    public const CONSTANT_STARTED = 'SESSION_STARTED_EVENT_CALLED';
    public const CONSTANT_AUTHENTICATED = 'USER_AUTHENTICATED_EVENT_CALLED';

    protected function wakeUp(): NamedClosure
    {
        return NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'handle_sessionInstantiated')),
            array($this, 'handle_sessionInstantiated')
        );
    }

    private function handle_sessionInstantiated(SessionInstantiatedEvent $event) : void
    {
        define(self::CONSTANT_INSTANTIATED, 'yes');

        $session = $event->getSession();
        $session->onSessionStarted(Closure::fromCallable(array($this, 'handle_sessionStarted')));
        $session->onUserAuthenticated(Closure::fromCallable(array($this, 'handle_userAuthenticated')));
    }

    private function handle_sessionStarted() : void
    {
        define(self::CONSTANT_STARTED, 'yes');
    }

    private function handle_userAuthenticated() : void
    {
        define(self::CONSTANT_AUTHENTICATED, 'yes');
    }
}
