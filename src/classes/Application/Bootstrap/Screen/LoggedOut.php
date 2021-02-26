<?php
/**
 * File containing the class {@see Application_Bootstrap_Screen_LoggedOut}.
 *
 * @package Application
 * @subpackage Bootstrap
 * @see Application_Bootstrap_Screen_LoggedOut
 */

declare(strict_types=1);

/**
 * Bootstrap screen that displays the logged out template
 * when the user successfully logged out, or has been redirected
 * here for some other reason (like not having sufficient rights
 * to access the application).
 *
 * The reason for the logout can optionally be specified in
 * the request, as an integer code of the type of logout (as
 * specified in the session class).
 *
 * @package Application
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see template_default_logged_out
 * @see Application_Session_Base
 * @see Application_Session_Base::LOGOUT_REASON_LOGIN_NOT_ALLOWED
 * @see Application_Session_Base::LOGOUT_REASON_USER_REQUEST
 */
class Application_Bootstrap_Screen_LoggedOut extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'logged-out.php';
    }
    
    protected function _boot()
    {
        $this->disableAuthentication();
        
        $this->createEnvironment();

        displayHTML(
            $this->driver
            ->getUI()
            ->createPage('logged-out')
            ->renderTemplate(
                'logged-out',
                array(
                    'reason-message' => $this->getMessage()
                )
            )
        );
    }

    private function getMessage() : ?UI_Message
    {
        if(!isset($_REQUEST['reason']))
        {
            return null;
        }

        $code = intval($_REQUEST['reason']);

        switch ($code)
        {
            case Application_Session_Base::LOGOUT_REASON_LOGIN_NOT_ALLOWED:
                return UI::getInstance()->createMessage(sb()
                    ->bold(t('You are not allowed to log in:'))
                    ->t('Your user account is missing the required right to log in.')
                )
                    ->makeError();
        }

        return null;
    }
}