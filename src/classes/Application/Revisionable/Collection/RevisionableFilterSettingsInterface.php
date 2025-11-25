<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use DBHelper\DBHelperFilterSettingsInterface;

interface RevisionableFilterSettingsInterface extends DBHelperFilterSettingsInterface
{
    public function getCollection() : RevisionableCollectionInterface;
}
