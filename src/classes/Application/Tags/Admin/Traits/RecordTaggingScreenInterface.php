<?php
/**
 * @package Tagging
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\Tags\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Tags\Taggables\TaggableInterface;

/**
 * @package Tagging
 * @subpackage Admin Screens
 * @see RecordTaggingScreenTrait
 */
interface RecordTaggingScreenInterface extends AdminScreenInterface
{
    public const SETTING_TAGS = 'tags';
    public const REQUEST_VAR_CLEAR_ALL = 'clear';

    public function getTaggableRecord() : TaggableInterface;

    public function getAdminCancelURL() : string;
    public function getAdminSuccessURL() : string;
}
