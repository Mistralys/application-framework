<?php
/**
 * @package Application
 * @subpackage Sessions
 */

declare(strict_types=1);

/**
 * Interface for the matching trait.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_OAuth
 * @see Application_Session_Base
 */
interface Application_Session_AuthTypes_OAuthInterface
{
    public const string TYPE_ID = 'OAuth';

    public const int ERROR_AUTH_DID_NOT_EXIT = 75601;
    public const int ERROR_USER_NO_EMAIL_ADDRESS = 75602;
}
