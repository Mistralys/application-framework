<?php
/**
 * File containing the trait {@see Application_Session_AuthTypes_None}.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session_AuthTypes_None
 */

declare(strict_types=1);

use Application\AppFactory;
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
    private array $fixedRights = array(
        Application_User::RIGHT_LOGIN,
        Application_User::RIGHT_DEVELOPER,
        Application_User::RIGHT_TRANSLATE_UI,
        Application_User::RIGHT_QA_TESTER
    );

    protected function handleLogin() : Application_Users_User
    {
        return AppFactory::createUsers()
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
            self::QA_TESTING_PRESET_ID => array(
                Application_User::RIGHT_LOGIN,
                Application_User::RIGHT_QA_TESTER
            )
        );
    }

    public function isRegistrationEnabled(): bool
    {
        return false;
    }

    public function fetchRights(Application_Users_User $user): array
    {
        return $this->fixedRights;
    }

    public function fetchSimulatedRights() : array
    {
        return $this->fixedRights;
    }

    public function getRightsString() : string
    {
        return implode(',', $this->fixedRights);
    }
}
