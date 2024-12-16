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
 * @see Application_Session_AuthTypes_None
 * @see Application_Session_Base
 */
interface Application_Session_AuthTypes_NoneInterface
{
    public const TYPE_ID = 'NoAuth';
}
