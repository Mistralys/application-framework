<?php
/**
 * @package Connectors
 * @subpackage Stub
 */

declare(strict_types=1);

namespace Connectors\Connector;

use AppUtils\ClassHelper\BaseClassHelperException;
use Connectors_Connector;
use Connectors\Connector\Stub\Method\StubFailureMethod;
use Connectors_Exception;

/**
 * @package Connectors
 * @subpackage Stub
 */
class StubConnector extends Connectors_Connector
{
    protected function checkRequirements(): void
    {

    }

    public function getURL(): string
    {
        return 'https://mistralys.eu';
    }

    /**
     * @return never
     * @throws BaseClassHelperException
     * @throws Connectors_Exception
     */
    public function executeFailRequest()
    {
        $method = $this->createMethod(StubFailureMethod::class);
        $method->failFetchData();
    }
}
