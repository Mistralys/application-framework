<?php

declare(strict_types=1);

namespace testsuites\SessionTests;

use Application_Session_Exception;
use Application_Session_Native;
use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver_Session;

class InitExceptionTest extends ApplicationTestCase
{
    /**
     * The session must throw an exception if it cannot be started.
     *
     * @see Application_Session_Native::start()
     */
    public function test_startSession() : void
    {
        $this->assertTrue(headers_sent(), 'Headers must already have been sent.');
        $this->assertTrue(isCLI());

        $session = new TestDriver_Session();

        $this->assertFalse($session->isEnabled(), 'In CLI mode, the session must not be enabled.');

        $session->start();

        $this->addToAssertionCount(1);
    }
}
