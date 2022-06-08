<?php
/**
 * @package Application
 * @subpackage Class loading
 * @see \Application\Exception\UnexpectedInstanceException
 */

declare(strict_types=1);

namespace Application\Exception;

use Application_Exception_UnexpectedInstanceType;

/**
 * @package Application
 * @subpackage Class loading
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UnexpectedInstanceException extends Application_Exception_UnexpectedInstanceType
{
    public const ERROR_UNEXPECTED_INSTANCE_TYPE = 63801;
}
