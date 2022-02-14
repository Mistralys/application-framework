<?php
/**
 * File containing the trait {@see Application_Session_AuthTypes_OAuth}.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session_AuthTypes_OAuth
 */

declare(strict_types=1);

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
    /**
     * @var Application_OAuth
     */
    protected $oauth;

    /**
     * @var Application_Request
     */
    protected $request;

    /**
     * @var \Hybridauth\Hybridauth
     */
    protected $authenticator;

    /**
     * Retrieves the foreign ID to use from the user profile information,
     * if any. Return an empty string if not needed.
     *
     * @param \Hybridauth\User\Profile $profile
     * @return string
     */
    abstract protected function getForeignID(\Hybridauth\User\Profile $profile) : string;

    public function handleLogin() : Application_Users_User
    {
        $this->request = $this->driver->getRequest();
        $this->oauth = new Application_OAuth($this->driver);
        $this->authenticator = $this->oauth->createAuthenticator();

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
            Application_Session_AuthTypes_CASInterface::ERROR_AUTH_DID_NOT_EXIT
        );
    }

    public function getRequestedName() : string
    {
        return strval(
            $this->request
                ->registerParam('strategy')
                ->setEnum($this->oauth->getAvailableNames())
                ->get()
        );
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

    private function autoLogin(\Hybridauth\Adapter\AdapterInterface $adapter) : Application_Users_User
    {
        $profile = $adapter->getUserProfile();
        $email = strval($profile->email);

        if(empty($email))
        {
            throw new OAuth_Exception(
                'Empty user data',
                'The user that is connected has no email address.',
                self::ERROR_USER_NO_EMAIL_ADDRESS
            );
        }

        return $this->registerUser(
            $email,
            strval($profile->firstName),
            strval($profile->lastName),
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
        displayHTML(
            $this->createTemplate('oauth/select-strategy')
                ->setVar('oauth', $this->oauth)
                ->render()
        );
    }
}
