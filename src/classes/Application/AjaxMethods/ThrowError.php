<?php

class Application_AjaxMethods_ThrowError extends Application_AjaxMethod
{
    public function processJSON()
    {
        throw new Application_Exception(
            'AJAX exception thrower',
            'Called the AJAX method that only throws an exception for testing.',
            43101
        );
    }
}
