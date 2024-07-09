<?php
/**
 * @package TestDriver
 * @see \TestDriver\Session\TestSessionCAS
 */

declare(strict_types=1);

namespace TestDriver\Session;

use Application\Session\NativeCASSession;
use Application_User;

/**
 * Test session class for CAS authentication. Used to
 * test the CAS authentication implementation using
 * any available CAS server.
 *
 * @package TestDriver
 */
class TestSessionCAS extends NativeCASSession
{
    /**
     * @var array<int,string>
     */
    protected array $simulateableUsers = array(
        1 => 'System',
        2 => 'Sample User',
    );

    /**
     * @var integer
     */
    protected int $defaultSimulatedUser = 2;

    protected function _getPrefix(): string
    {
        return 'appframework_test_';
    }

    public function getEmailField(): string
    {
        return TESTS_CAS_FIELD_EMAIL;
    }

    public function getFirstnameField(): string
    {
        return TESTS_CAS_FIELD_FIRST_NAME;
    }

    public function getLastnameField(): string
    {
        return TESTS_CAS_FIELD_LAST_NAME;
    }

    public function getForeignIDField(): string
    {
        return TESTS_CAS_FIELD_FOREIGN_ID;
    }

    public function fetchRights(Application_User $user): array
    {
        return array();
    }

    public function isRegistrationEnabled(): bool
    {
        return true;
    }
}
