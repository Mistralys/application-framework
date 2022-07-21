<?php
/**
 * File containing the trait {@see Application_Session_AuthTypes_None}.
 *
 * @package Application
 * @subpackage Sessions
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
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_NoneInterface
 * @see Application_Session_Base
 */
trait Application_Session_AuthTypes_None
{
    /**
     * @var string[]
     */
    private $fixedRights = array(
        Application_User::RIGHT_LOGIN,
        Application_User::RIGHT_DEVELOPER,
        Application_User::RIGHT_TRANSLATE_UI
    );

    protected function handleLogin() : Application_Users_User
    {
        return Application_Driver::createUsers()
            ->getByID(Application::USER_ID_SYSTEM);
    }

    /**
     * @return array<string,array<int,string>>
     */
    public function getRightPresets() : array
    {
        return array(
            self::ADMIN_PRESET_ID => array(
                Application_User::RIGHT_LOGIN,
                Application_User::RIGHT_DEVELOPER
            ),
        );
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
        return $this->fixedRights;
    }

    public function getCurrentRights() : string
    {
        return implode(
            ',',
            $this->fixedRights
        );
    }
}
