<?php
/**
 * File containing the trait {@see Application_Session_AuthTypes_CAS}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_Session_AuthTypes_CAS
 */

declare(strict_types=1);

/**
 * Drop-in trait for sessions using CAS authentication.
 *
 * Usage:
 *
 * 1) Use this trait in your application session class
 * 2) Implement the matching interface
 * 3) Add the required configuration settings:
 *    - APP_CAS_HOST
 *    - APP_CAS_PORT
 *    - APP_CAS_SERVER
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
        phpCAS::client(
            CAS_VERSION_2_0,
            APP_CAS_HOST,
            APP_CAS_PORT,
            APP_CAS_SERVER
        );

        phpCAS::SetNoCasServerValidation();

        if (!phpCAS::isAuthenticated()) {
            header("Location: " . phpCAS::getServerLoginURL());
            exit;
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
        
        $this->log(sprintf(
            'Information found in the request, registering the user %1$s in the session.',
            $email
        ));

        return $this->registerUser(
            $email,
            strval(phpCAS::getAttribute($this->getFirstnameField())),
            strval(phpCAS::getAttribute($this->getLastnameField())),
            strval(phpCAS::getAttribute($this->getForeignIDField()))
        );
    }
}
