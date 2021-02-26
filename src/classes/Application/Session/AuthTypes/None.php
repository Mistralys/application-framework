<?php
/**
 * File containing the trait {@see Application_Session_AuthTypes_None}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_Session_AuthTypes_None
 */

declare(strict_types=1);

use Hybridauth\User\Profile;

/**
 * Use this as drop-in trait for the application's session class
 * when the application does not require any authentication: it
 * uses the system user to simulate the logged in user.
 *
 * @package Application
 * @subpackage Session
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_NoneInterface
 * @see Application_Session_Base
 */
trait Application_Session_AuthTypes_None
{
    protected function handleLogin() : Application_Users_User
    {
        $users = Application_Driver::createUsers();

        return $users->getByID(Application::USER_ID_SYSTEM);
    }

    protected function getForeignID(Profile $profile): string
    {
        return '__none';
    }

    public function isRegistrationEnabled(): bool
    {
        return false;
    }

    public function fetchRights(Application_Users_User $user): array
    {
        return array(
            Application_User::RIGHT_LOGIN,
            Application_User::RIGHT_DEVELOPER,
            Application_User::RIGHT_TRANSLATE_UI
        );
    }
}
