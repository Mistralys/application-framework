<?php
/**
 * @package TestDriver
 * @see \TestDriver\Session\TestSessionNoAuth
 */

declare(strict_types=1);

namespace TestDriver\Session;

use Application_Session_AuthTypes_None;
use Application_Session_AuthTypes_NoneInterface;
use Application_Session_Native;

/**
 * Default session handling for the test application: Do not use
 * any authentication (implemented via {@see Application_Session_AuthTypes_None}).
 * Does not require any configuration.
 *
 * @package TestDriver
 */
class TestSessionNoAuth
    extends Application_Session_Native
    implements Application_Session_AuthTypes_NoneInterface
{
    use Application_Session_AuthTypes_None;

    protected function _getPrefix(): string
    {
        return 'appframework_test_';
    }
}
