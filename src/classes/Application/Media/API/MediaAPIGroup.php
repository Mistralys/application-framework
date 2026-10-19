<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\Groups\GenericAPIGroup;

/**
 * API method group for the media document API methods.
 *
 * @package Media
 * @subpackage API
 */
class MediaAPIGroup extends GenericAPIGroup
{
    private function __construct()
    {
        parent::__construct(
            'media',
            'Media',
            'Upload and manage media documents.'
        );
    }

    private static ?self $instance = null;

    public static function create() : self
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
