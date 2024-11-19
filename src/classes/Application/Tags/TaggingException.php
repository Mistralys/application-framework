<?php
/**
 * @package Tagging
 * @subpackage Exceptions
 */

declare(strict_types=1);

namespace Application\Tags;

use Application_Exception;

/**
 * @package Tagging
 * @subpackage Exceptions
 */
class TaggingException extends Application_Exception
{
    public const ERROR_ROOT_TAG_NOT_SET = 153701;
    public const ERROR_INVALID_UNIQUE_ID = 153702;
    public const ERROR_TAGGABLE_NOT_FOUND = 153703;
}
