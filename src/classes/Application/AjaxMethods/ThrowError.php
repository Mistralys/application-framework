<?php

class Application_AjaxMethods_ThrowError extends Application_AjaxMethod
{
    public const METHOD_NAME = 'ThrowError';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        throw new Application_Exception(
            'AJAX exception thrower',
            'Called the AJAX method that only throws an exception for testing.',
            43101
        );
    }
}
