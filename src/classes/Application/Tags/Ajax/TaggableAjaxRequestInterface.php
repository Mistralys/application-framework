<?php
/**
 * @package Tagging
 * @subpackage Ajax Methods
 */

declare(strict_types=1);

namespace Application\Tags\Ajax;

use Application\Ajax\AjaxMethodInterface;
use Application\Tags\Taggables\TaggableUniqueID;

/**
 * Interface for the trait {@see TaggableAjaxRequestTrait}.
 *
 * @package Tagging
 * @subpackage Ajax Methods
 */
interface TaggableAjaxRequestInterface extends AjaxMethodInterface
{
    public const PARAM_UNIQUE_ID = 'unique_id';
    public const VALIDATION_TAGGABLE_NOT_FOUND = 167901;

    public function getUniqueID() : ?TaggableUniqueID;
}
