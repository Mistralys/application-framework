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

        try
        {
            // Attempt to start a new session, which must fail because
            // headers have already been sent at this point.
            $session = new TestDriver_Session();
            $session->start();
        }
        catch (Application_Session_Exception $e)
        {
            $this->assertSame(Application_Session_Native::ERROR_CANNOT_START_SESSION, $e->getCode());
            $this->assertSame(Application_Session_Native::SESSION_HEADERS_ALREADY_SENT, $e->getErrorCode());
            return;
        }

        $this->fail('No exception thrown.');
    }
}
