<?php
/**
 * @package Application
 * @subpackage AJAX
 */

declare(strict_types=1);

namespace Application\Ajax;

use Application_AjaxMethod;
use AppUtils\ArrayDataCollection;

/**
 * Base class for AJAX methods that return JSON responses.
 *
 * @package Application
 * @subpackage AJAX
 */
abstract class BaseJSONAjaxMethod extends Application_AjaxMethod
{
    public function processJSON() : void
    {
        $payload = ArrayDataCollection::create();

        $this->collectPayload($payload);

        $this->sendResponse($payload);
    }

    abstract protected function collectPayload(ArrayDataCollection $payload) : void;
}
