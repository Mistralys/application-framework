<?php

declare(strict_types=1);

namespace AppFrameworkTests\SessionTests;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application_Session_Base;
use Application_Session_Native;

final class NativeTests extends ApplicationTestCase
{
    // region: _Tests

    public function test_logOut() : void
    {
        $_SESSION['fooyo'] = 'baryo';

        $this->assertNotNull($this->session->getUser());

        // To avoid exiting the application on logout.
        Application_Session_Base::setRedirectsEnabled(false);

        $this->session->logOut();

        $this->assertEmpty($_SESSION);
    }

    public function test_unsetValue() : void
    {
        $_SESSION['test'] = 'foooo';

        $this->session->unsetValue('test');

        $this->assertArrayNotHasKey('test', $_SESSION);
    }

    // endregion

    // region: Support methods

    private Application_Session_Native $session;

    public function setUp() : void
    {
        parent::setUp();

        $_SESSION = array();

        $session = AppFactory::createSession();

        $this->assertInstanceOf(Application_Session_Native::class, $session);
        $this->assertTrue($session->isStarted());

        $this->session = $session;
    }

    // endregion
}