<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Session;

use Application_Session_AuthTypes_OAuth;
use Application_Session_AuthTypes_OAuthInterface;
use Application_Session_Native;
use Application_User;
use Application_Users_User;
use Hybridauth\User\Profile;

/**
 * Session stub class for OAuth authentication type.
 * Used for static code analysis and testing.
 */
class OAuthSessionStub extends Application_Session_Native implements Application_Session_AuthTypes_OAuthInterface
{
    use Application_Session_AuthTypes_OAuth;

    protected function _getName(): string
    {
        return 'session_oauth_stub';
    }

    protected function sendAuthenticationCallbacks(): ?Application_Users_User
    {
        return null;
    }

    protected function getForeignID(Profile $profile): string
    {
        return 'foreign_id_stub';
    }

    public function fetchRights(Application_User $user): array
    {
        return array();
    }

    public function isRegistrationEnabled(): bool
    {
        return false;
    }
}
