<?php

declare(strict_types=1);

namespace AppFrameworkIntegrationTests\Logging;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use AppUtils\ArrayDataCollection;
use AppUtils\OperationResult;

final class LogOutputTest extends ApplicationTestCase
{
    public function test_outputs() : void
    {
        $this->enableLogging();

        $logger = AppFactory::createLogger();

        $logger->logData(array('foo' => 'bar'));

        $logger->logData(ArrayDataCollection::create(array('data' => 'test')));

        $logger->logData((new OperationResult($this))->makeNotice('Success!', 12345));

        $this->addToAssertionCount(1);
    }
}
