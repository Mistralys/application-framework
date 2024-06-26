<?php

declare(strict_types=1);

namespace testsuites\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application;
use Application\AppFactory;
use Application_Bootstrap;
use Application_Exception;
use Application_Logger;
use TestDriver_Bootstrap_Screen_ExceptionTest;
use TestLoggable;

final class LoggingTests extends ApplicationTestCase
{
   /**
    * Ensure that regular exceptions get converted to application
    * exceptions, so they can be logged.
    */
    public function test_convertException() : void
    {
        try
        {
            // The screen throws a regular Exception.
            Application_Bootstrap::bootClass(TestDriver_Bootstrap_Screen_ExceptionTest::class, array(), false);
            
            $this->fail('No exception was triggered.');
        }
        catch(Application_Exception $e)
        {
            $this->assertEquals(Application_Bootstrap::ERROR_NON_FRAMEWORK_EXCEPTION, $e->getCode());

            $previous = $e->getPrevious();
            $this->assertNotNull($previous);

            $this->assertEquals(
                TestDriver_Bootstrap_Screen_ExceptionTest::ERROR_TEST_EXCEPTION,
                $previous->getCode(),
                Application_Exception::getDeveloperMessage($previous)
            );
        }
    }
    
   /**
    * Ensures that if exceptions are not set to be thrown,
    * the error page is displayed.
    * 
    * @see displayError()
    * @see Application_Bootstrap::bootClass()
    */
    public function test_displayException() : void
    {
        // TODO Review this test for the exit bypass
        $this->markTestIncomplete();

        // disable exiting, since the error page calls exit.
        $restore = Application::setExitEnabled(false);
        
        Application_Bootstrap::bootClass(TestDriver_Bootstrap_Screen_ExceptionTest::class);
        
        // PHPUnit captures output of tests by default. This allows us
        // to check the generated content using a regex - in this case,
        // we look for the title of the error page (which is used both in
        // text and HTML mode).
        $this->expectOutputRegex(sprintf(
            '/%s/six',
            str_replace(' ', '\s+', APP_ERROR_PAGE_TITLE)
        ));
        
        // restore the exit setting to the previous value
        Application::setExitEnabled($restore);
    }

    /**
     * The logging methods support adding arbitrary arguments,
     * which are injected into the message using `sprintf()`.
     * This must work with all methods.
     */
    public function test_loggable_messageArguments() : void
    {
        $logger = AppFactory::createLogger();
        $logger->clearLog();

        $loggable = new TestLoggable();

        $loggable->addLogMessage('Test message');
        $this->assertStringContainsString('Test message', $logger->getLastMessage());

        $loggable->addLogMessage('With %s injected values', 'foo');
        $this->assertStringContainsString('With foo injected values', $logger->getLastMessage());

        $loggable->addEventLog('EventName', 'Foo%s', 'bar');
        $this->assertStringContainsString('Foobar', $logger->getLastMessage());

        $loggable->addDataLog(array('FooBar' => 'Value'));
        $this->assertStringContainsString('FooBar', $logger->getLastMessage());

        $loggable->addErrorLog('%s error message', 'Foo');
        $this->assertStringContainsString('Foo error message', $logger->getLastMessage());

        // clear the log for checking the header result
        $logger->clearLog();

        $loggable->addHeaderLog('Supah %s header', 'foo');
        $this->assertStringContainsString('SUPAH FOO HEADER', implode('', $logger->getLog()));
    }

    public function test_loggingEnabled() : void
    {
        $logger = AppFactory::createLogger();

        $this->assertTrue($logger->isLoggingEnabled(), 'The default is to have the runtime logging enabled.');

        $logger->setLoggingEnabled(false);
        $logger->clearLog();

        $logger->log('Test message');

        $this->assertFalse($logger->isLoggingEnabled());
        $this->assertEmpty($logger->getLog(), 'No log entries must be present after disabling logging.');
    }

    public function test_disableMemoryLogging() : void
    {
        $logger = AppFactory::createLogger();

        $this->assertTrue($logger->isMemoryStorageEnabled(), 'The default is to have the memory logging enabled.');
        $this->assertEmpty($logger->getLog(), 'No log entries must be present after creating the logger.');

        $logger->log('First message');

        $this->assertNotEmpty($logger->getLog(), 'Log entries must be present after logging a message.');

        $logger->clearLog();
        $logger->setMemoryStorageEnabled(false);

        $logger->log('Test message');

        $this->assertEmpty($logger->getLog(), 'No log entries must be present after disabling memory logging.');
    }

    public function test_setCategoryEnabled() : void
    {
        $logger = AppFactory::createLogger();

        $this->assertTrue($logger->isCategoryEnabled(Application_Logger::CATEGORY_UI), 'The default is to have the UI category enabled.');
        $logger->logUI('First UI test');
        $this->assertNotEmpty($logger->getLog());

        $logger->clearLog();

        $logger->setCategoryEnabled(Application_Logger::CATEGORY_UI, false);
        $logger->logUI('Test UI message');
        $this->assertEmpty($logger->getLog(), 'No log entries must be present after disabling the UI category.');
    }
}
