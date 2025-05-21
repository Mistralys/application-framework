<?php
/**
 * @package TestDriver
 * @subpackage AJAX
 */

declare(strict_types=1);

namespace TestDriver\Connectors;

use Application_Bootstrap_Screen_Ajax;
use Connectors_Connector;

/**
 * Test connector used to make calls to the application's
 * own available AJAX methods.
 *
 * @package TestDriver
 * @subpackage AJAX
 */
class InternalAjaxConnector extends Connectors_Connector
{
    protected function checkRequirements(): void
    {
    }

    public function getURL(): string
    {
        return APP_URL.'/'. Application_Bootstrap_Screen_Ajax::DISPATCHER;
    }
}
