<?php
/**
 * @package Connectors
 * @subpackage Stub
 */

declare(strict_types=1);

namespace Connectors\Connector;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use Connectors\Connector\Stub\Method\StubFailureMethod;

/**
 * @package Connectors
 * @subpackage Stub
 */
class StubConnector extends BaseConnector
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
     * @throws ConnectorException
     */
    public function executeFailRequest()
    {
        $method = ClassHelper::requireObjectInstanceOf(
            StubFailureMethod::class,
            $this->createMethod(StubFailureMethod::class)
        );

        $method->failFetchData();
    }
}
