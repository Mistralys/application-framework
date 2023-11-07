<?php
/**
 * @package Application Tests
 * @subpackage Logging
 * @see \testsuites\Application\CASLoggingTests
 */

declare(strict_types=1);

namespace testsuites\Application;

use Application\AppFactory;
use Application\Logger\PSRLogger;
use AppFrameworkTestClasses\ApplicationTestCase;
use Application_Logger;
use phpCAS;

/**
 * Ensures that CAS logging messages are correctly redirected
 * to the main application log.
 *
 * @package Application Tests
 * @subpackage Logging
 * @covers Application_Session_AuthTypes_CAS
 */
class CASLoggingTests extends ApplicationTestCase
{
    // region: _Tests

    public function test_logCapturesMessages() : void
    {
        phpCAS::setLogger(new PSRLogger('TestsuiteLogger'));

        phpCAS::trace('Test trace message');

        $log = $this->logger->getLog();

        $this->assertNotEmpty($log);

        $haystack = implode(' ', $log);

        $this->assertStringContainsString('TestsuiteLogger', $haystack);
        $this->assertStringContainsString('Test trace message', $haystack);
    }

    // endregion

    // region: Support methods

    protected Application_Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = AppFactory::createLogger();
        $this->logger->clearLog();
    }

    // endregion
}
