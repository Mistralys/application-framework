<?php
/**
 * @package Application
 * @subpackage Sessions
 */

declare(strict_types=1);

use Application\AppFactory;
use Hybridauth\User\Profile;

/**
 * Use this as a drop-in trait for the application's session class
 * when the application does not require any authentication: it
 * uses the system user to simulate the logged-in user.
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

    protected function sendAuthenticationCallbacks() : Application_Users_User
    {
        return AppFactory::createUsers()->getSystemUser();
    }

    public function isRegistrationEnabled(): bool
    {
        return false;
    }

    public function fetchRights(Application_User $user): array
    {
        return $this->fixedRights;
    }

    public function getRightsString() : string
    {
        return implode(',', $this->fixedRights);
    }

    protected function redirectToReturnURI(bool $authActive) : void
    {
        // Do nothing: Without authentication, there is no need to redirect
        // to a return URI, as there are no callbacks between services.
    }
}
