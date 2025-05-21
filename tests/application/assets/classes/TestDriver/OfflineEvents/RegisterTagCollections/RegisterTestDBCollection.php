<?php
/**
 * @package TestDriver
 * @subpackage TestDBCollection
 */

declare(strict_types=1);

namespace TestDriver\OfflineEvents\RegisterTagCollections;

use Application\Tags\Events\BaseRegisterTagCollectionsListener;
use TestDriver\TestDBRecords\TestDBCollection;

/**
 * @package TestDriver
 * @subpackage TestDBCollection
 */
class RegisterTestDBCollection extends BaseRegisterTagCollectionsListener
{
    protected function getCollections(): array
    {
        return array(TestDBCollection::getInstance());
    }
}
