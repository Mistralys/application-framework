<?php
/**
 * File containing the interface {@see Application_Session_AuthTypes_CASInterface}.
 *
 * @package Application
 * @subpackage Session
 * @see Application_Session_AuthTypes_CASInterface
 */

declare(strict_types=1);

/**
 * Interface for the matching trait.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Session_AuthTypes_CAS
 * @see Application_Session_Base
 */
interface Application_Session_AuthTypes_CASInterface
{
    public const TYPE_ID = 'CAS';
    public const ERROR_EMPTY_USER_INFO = 75501;
    public const ERROR_CAS_CLIENT_INIT_FAILED = 75502;

    public function getEmailField() : string;
    public function getFirstnameField() : string;
    public function getLastnameField() : string;
    public function getForeignIDField() : string;
}
