<?php
/**
 * @package TestDriver
 * @subpackage AJAX
 */

declare(strict_types=1);

namespace TestDriver\Connectors\InternalAjax;

use Connectors_Connector_Method_Get;
use Connectors_Response;
use TestDriver\AjaxMethods\AjaxGetTestJSON;

/**
 * Test connector method to fetch the response from
 * the AJAX method {@see AjaxGetTestJSON}.
 *
 * @package TestDriver
 * @subpackage AJAX
 */
class GetTestJSONMethod extends Connectors_Connector_Method_Get
{
    public function unknownMethod() : Connectors_Response
    {
        return $this->createMethodRequest('UnknownMethod')->getData();
    }

    public function knownMethod() : Connectors_Response
    {
        return $this->createMethodRequest(AjaxGetTestJSON::METHOD_NAME)->getData();
    }
}
