<?php
/**
 * @package Application
 * @subpackage Sessions
 */

declare(strict_types=1);

use Application\AppFactory;
use Hybridauth\Adapter\AdapterInterface;
use Hybridauth\User\Profile;

/**
 * Drop-in trait for using OpenAuth authentication.
 *
 * Usage:
 *
 * 1) Use this trait in the application's session class
 * 2) Implement the matching interface
 * 3) In the `classes/DriverName/OAuth` folder, create the strategy classes
 *
 * The strategy classes must extend the existing strategies
 * in the framework. These have abstract methods that need
 * to be implemented for them to work. Simply add classes for
 * those that you wish to use.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_OAuthInterface
 * @see Application_Session_Base
 *
 * @see Application_OAuth_Strategy_Facebook
 * @see Application_OAuth_Strategy_GitHub
 * @see Application_OAuth_Strategy_Google
 */
trait Application_Session_AuthTypes_OAuth
{
    protected Application_OAuth $oauth;
    protected Application_Request $request;

    /**
     * Retrieves the foreign ID to use from the user profile information,
     * if any. Return an empty string if not needed.
     *
     * @param Profile $profile
     * @return string
     */
    abstract protected function getForeignID(Profile $profile) : string;

    protected function sendAuthenticationCallbacks(): ?Application_Users_User
    {
        $driver = AppFactory::createDriver();
        $this->request = $driver->getRequest();
        $this->oauth = new Application_OAuth($driver);

        $user = $this->checkLoginState();

        if($user !== null)
        {
            return $user;
        }

        $name = $this->getRequestedName();

        if(!empty($name))
        {
            $this->handleAuthentication($this->oauth->getByName($name));
        }
        else
        {
            $this->handleLoginScreen();
        }

        throw new Application_Exception(
            'Authentication did not exit',
            'The authentication must either show the login screen, or authenticate a user.',
            Application_Session_AuthTypes_OAuthInterface::ERROR_AUTH_DID_NOT_EXIT
        );
    }

    public function getRequestedName() : string
    {
        return (string)$this->request
            ->registerParam('strategy')
            ->setEnum($this->oauth->getAvailableNames())
            ->get();
    }

    private function checkLoginState() : ?Application_Users_User
    {
        $adapter = $this->oauth->isConnected();

        if($adapter !== null)
        {
            return $this->autoLogin($adapter);
        }

        return null;
    }

    private function autoLogin(AdapterInterface $adapter) : Application_Users_User
    {
        $profile = $adapter->getUserProfile();
        $email = (string)$profile->email;

        if(empty($email))
        {
            throw new OAuth_Exception(
                'Empty user data',
                'The user that is connected has no email address.',
                Application_Session_AuthTypes_OAuthInterface::ERROR_USER_NO_EMAIL_ADDRESS
            );
        }

        return $this->registerUser(
            $email,
            (string)$profile->firstName,
            (string)$profile->lastName,
            $this->getForeignID($profile)
        );
    }

    /**
     * This redirects to the authentication screen for the selected
     * login strategy.
     *
     * @param Application_OAuth_Strategy $strategy
     * @throws \Hybridauth\Exception\InvalidArgumentException
     * @throws \Hybridauth\Exception\UnexpectedValueException
     */
    private function handleAuthentication(Application_OAuth_Strategy $strategy) : void
    {
        $auth = $this->oauth->createAuthenticator($strategy);
        $auth->authenticate($strategy->getName());
    }

    /**
     * Displays the login screen which allows selecting
     * the login strategy to use.
     *
     * @see template_default_oauth_select_strategy
     */
    private function handleLoginScreen() : void
    {
        $page = new UI_Page(UI::getInstance(), 'oauth-login-screen');

        displayHTML(
            $page->createTemplate('oauth/select-strategy')
                ->setVar('oauth', $this->oauth)
                ->render()
        );
    }

    public function getAuthTypeID(): string
    {
        return Application_Session_AuthTypes_OAuthInterface::TYPE_ID;
    }
}
