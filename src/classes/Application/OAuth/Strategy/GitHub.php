<?php
/**
 * File containing the class {@see Application_OAuth_Strategy_GitHub}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_OAuth_Strategy_GitHub
 */

declare(strict_types=1);

/**
 *
 * @package Application
 * @subpackage Session
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link http://developer.github.com/v3/oauth/
 */
abstract class Application_OAuth_Strategy_GitHub extends Application_OAuth_Strategy
{
    abstract public function getClientID() : string;
    abstract public function getClientSecret() : string;

    public function getLabel(): string
    {
        return 'GitHub';
    }

    public function getConfig() : array
    {
        return array(
            'id' => $this->getClientID(),
            'secret' => $this->getClientSecret()
        );
    }
}
