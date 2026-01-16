<?php

use Application\AppFactory;

class Application_AjaxMethods_GetLookupItems extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'GetLookupItems';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $payload = array();
        
        $lookup = AppFactory::createLookupItems();
        $items = $lookup->getItems();
        foreach($items as $item) {
            $payload[] = $item->toArray();
        }
        
        $this->sendResponse($payload);
    }
}