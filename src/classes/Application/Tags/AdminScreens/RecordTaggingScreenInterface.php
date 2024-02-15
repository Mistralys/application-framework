<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags\AdminScreens;

use Application\Tags\Taggables\TaggableInterface;
use Application_Admin_ScreenInterface;

/**
 * @package Application
 * @subpackage Tags
 * @see RecordTaggingScreenTrait
 */
interface RecordTaggingScreenInterface extends Application_Admin_ScreenInterface
{
    public const SETTING_TAGS = 'tags';
    public const REQUEST_VAR_CLEAR_ALL = 'clear';

    public function getTaggableRecord() : TaggableInterface;

    public function getAdminCancelURL() : string;
    public function getAdminSuccessURL() : string;
}
