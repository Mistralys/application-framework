<?php
/**
 * File containing the interface {@see Application_Session_AuthTypes_OAuthInterface}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_Session_AuthTypes_OAuthInterface
 */

declare(strict_types=1);

/**
 * Interface for the matching trait.
 *
 * @package Application
 * @subpackage Session
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_OAuth
 * @see Application_Session_Base
 */
interface Application_Session_AuthTypes_OAuthInterface
{
    public const ERROR_AUTH_DID_NOT_EXIT = 75601;

}
