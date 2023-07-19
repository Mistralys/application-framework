<?php
/**
 * @package Application
 * @subpackage Sessions
 * @see \Application\Session\NativeCASSession
 */

declare(strict_types=1);

namespace Application\Session;

use Application_Session_AuthTypes_CAS;
use Application_Session_AuthTypes_CASInterface;
use Application_Session_Native;

/**
 * Session implementation using the native PHP session functions.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class NativeCASSession extends Application_Session_Native implements Application_Session_AuthTypes_CASInterface
{
    use Application_Session_AuthTypes_CAS;
}
