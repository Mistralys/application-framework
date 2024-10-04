<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags\AdminScreens;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Tags\Taggables\TaggableInterface;

/**
 * @package Application
 * @subpackage Tags
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
