<?php
/**
 * File containing the trait {@see Application_Session_AuthTypes_CAS}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_Session_AuthTypes_CAS
 */

declare(strict_types=1);

use function AppUtils\parseURL;

/**
 * Drop-in trait for sessions using CAS authentication.
 *
 * Usage:
 *
 * 1) Use this trait in your application session class
 * 2) Implement the matching interface
 * 3) Add the required configuration settings:
 *    - <code>APP_CAS_HOST</code> The CAS server host name, e.g. <code>cas.example.com</code>
 *    - <code>APP_CAS_PORT</code> The CAS server port. Default is <code>443</code>.
 *    - <code>APP_CAS_SERVER</code> URL to reach the service, e.g. <code>https://cas.example.com:443/login</code>
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_CASInterface
 * @see Application_Session_Base
 */
trait Application_Session_AuthTypes_CAS
{
    abstract public function getEmailField() : string;
    abstract public function getFirstnameField() : string;
    abstract public function getLastnameField() : string;
    abstract public function getForeignIDField() : string;

    /**
     * Handles a user's login when no user is present in the editor's
     * session: redirects to the intranet login page and also processes
     * the response from the login page.
     *
     * Can throw an exception if one the required queries fail.
     *
     * @throws Application_Exception
     */
    protected function handleLogin() : Application_Users_User
    {
        $this->log('Initializing CAS client.');

        // Build the base URL dynamically, because it must only
        // contain the scheme and host, not the path. The path
        // is added by the CAS client.
        $url = parseURL(APP_URL);
        $baseURL = $url->getScheme().'://'.$url->getHost();

        $this->log('CAS Host: '.APP_CAS_HOST.':'.APP_CAS_PORT);
        $this->log('CAS Server URI: '.APP_CAS_SERVER);
        $this->log('CAS Client Base URL: '.$baseURL);

        // ARRRRRRRRGH. Who in the blazing, purple hell terminates
        // the script by default when an exception is thrown, and
        // even has the gall to call that graceful?!
        CAS_GracefullTerminationException::throwInsteadOfExiting();

        try
        {
            phpCAS::client(
                CAS_VERSION_2_0,
                APP_CAS_HOST,
                APP_CAS_PORT,
                APP_CAS_SERVER,
                $baseURL
            );
        }
        catch (Throwable $e)
        {
            throw new Application_Exception(
                'CAS client initialization failed',
                sprintf(
                    'Failed to initialize the CAS client. The error was: %s',
                    $e->getMessage()
                ),
                Application_Session_AuthTypes_CASInterface::ERROR_CAS_CLIENT_INIT_FAILED,
                $e
            );
        }

        $this->log('CAS client initialized.');

        $client = phpCAS::getCasClient();
        $client->setBaseURL(rtrim(APP_CAS_SERVER, '/').'/');

        phpCAS::SetNoCasServerValidation();

        if (!phpCAS::isAuthenticated())
        {
            $this->log('User not authenticated, redirecting to CAS login page.');

            $loginURL = phpCAS::getServerLoginURL();

            $this->log('Target URL: '.$loginURL);

            header("Location: " . $loginURL);
            Application::exit('Redirecting to CAS login page.');
        }

        $email = phpCAS::getAttribute($this->getEmailField());

        if (empty($email))
        {
            throw new Application_Exception(
                'Empty user information',
                sprintf(
                    'Empty user email. The field [%s] was empty.',
                    $this->getEmailField()
                ),
                Application_Session_AuthTypes_CASInterface::ERROR_EMPTY_USER_INFO
            );
        }
        
        $this->log(
            'Information found in the request, registering the user %1$s in the session.',
            $email
        );

        return $this->registerUser(
            $email,
            (string)phpCAS::getAttribute($this->getFirstnameField()),
            (string)phpCAS::getAttribute($this->getLastnameField()),
            (string)phpCAS::getAttribute($this->getForeignIDField())
        );
    }
}
