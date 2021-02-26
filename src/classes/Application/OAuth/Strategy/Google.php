<?php
/**
 * File containing the class {@see Application_OAuth_Strategy_Google}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_OAuth_Strategy_Google
 */

declare(strict_types=1);

/**
 * Extend this class in the application to use Google authentication.
 *
 * @package Application
 * @subpackage Session
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://developers.google.com/accounts/docs/OAuth2
 * @link https://code.google.com/apis/console/
 */
abstract class Application_OAuth_Strategy_Google extends Application_OAuth_Strategy
{
    abstract public function getClientID() : string;
    abstract public function getClientSecret() : string;

    public function getLabel(): string
    {
        return 'Google';
    }

    public function getConfig() : array
    {
        return array(
            'id' => $this->getClientID(),
            'secret' => $this->getClientSecret()
        );
    }
}
