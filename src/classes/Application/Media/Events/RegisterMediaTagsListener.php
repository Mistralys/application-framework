<?php
/**
 * @package Application
 * @subpackage Media
 */

declare(strict_types=1);

namespace Application\Media\Events;

use Application\AppFactory;
use Application\Tags\Events\BaseRegisterTagCollectionsListener;

/**
 * Registers the media documents collection as a tag collection.
 *
 * @package Application
 * @subpackage Media
 */
class RegisterMediaTagsListener extends BaseRegisterTagCollectionsListener
{
    protected function getCollections(): array
    {
        return array(
            AppFactory::createMedia(),
            AppFactory::createMediaCollection()
        );
    }
}
