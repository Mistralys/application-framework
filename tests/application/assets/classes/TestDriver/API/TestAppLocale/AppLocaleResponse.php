<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API\TestAppLocale;

use Application\API\ResponsePayload;
use TestDriver\API\TestAppLocaleMethod;

/**
 * @package TestDriver
 * @subpackage API
 *
 * @method TestAppLocaleMethod getMethod()
 */
class AppLocaleResponse extends ResponsePayload
{
    public function __construct(TestAppLocaleMethod $method, array $data = array())
    {
        parent::__construct($method, $data);
    }

    public function getText() : string
    {
        return $this->getString(TestAppLocaleMethod::KEY_TEXT);
    }
}
