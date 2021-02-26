<?php
/**
 * File containing the class {@see Application_OAuth_Strategy_Facebook}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_OAuth_Strategy_Facebook
 */

declare(strict_types=1);

/**
 *
 * @package Application
 * @subpackage Session
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://developers.facebook.com/docs/authentication/
 */
abstract class Application_OAuth_Strategy_Facebook extends Application_OAuth_Strategy
{
    abstract public function getAppID() : string;
    abstract public function getAppSecret() : string;

    public function getLabel(): string
    {
        return 'Facebook';
    }

    public function getConfig() : array
    {
        return array(
            'id' => $this->getAppID(),
            'secret' => $this->getAppSecret(),
        );
    }
}
