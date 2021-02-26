<?php

use PHPUnit\Framework\TestCase;

final class Application_LoggingTest extends TestCase
{
   /**
    * Ensure that regular exceptions get converted to application
    * exceptions, so they can be logged.
    */
    public function test_convertException()
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
            $this->assertEquals(TestDriver_Bootstrap_Screen_ExceptionTest::ERROR_TEST_EXCEPTION, $e->getPrevious()->getCode());
        }
    }
    
   /**
    * Ensures that if exceptions are not set to be thrown,
    * the error page is displayed.
    * 
    * @see displayError()
    * @see Application_Bootstrap::bootClass()
    */
    public function test_displayException()
    {
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
}
