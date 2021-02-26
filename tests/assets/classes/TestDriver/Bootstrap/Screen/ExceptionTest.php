<?php

class TestDriver_Bootstrap_Screen_ExceptionTest extends Application_Bootstrap_Screen
{
    const ERROR_TEST_EXCEPTION = 55901;
    
    public function getDispatcher()
    {
        return '';
    }
    
    protected function _boot()
    {
        throw new Exception('exceptiontest', self::ERROR_TEST_EXCEPTION);
    }
}
