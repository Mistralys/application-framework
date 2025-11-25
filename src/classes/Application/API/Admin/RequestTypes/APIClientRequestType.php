<?php
/**
 * @package API
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\API\Admin\RequestTypes;

use Application\API\Clients\APIClientRecord;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use DBHelper\Admin\Requests\BaseDBRecordRequestType;
use UI\AdminURLs\AdminURLInterface;

/**
 * Request type for API Client records, used to fetch an
 * API record based on the request.
 *
 * @package API
 * @subpackage Admin
 *
 * @method APIClientRecord|NULL getRecord()
 * @method APIClientRecord getRecordOrRedirect()
 * @method APIClientRecord requireRecord()
 */
class APIClientRequestType extends BaseDBRecordRequestType
{
    public function getCollection(): APIClientsCollection
    {
        return AppFactory::createAPIClients();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->getCollection()->adminURL()->list();
    }
}
