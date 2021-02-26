<?php

require_once 'Application/AjaxMethod.php';

class Application_AjaxMethods_GetLookupItems extends Application_AjaxMethod
{
    public function processJSON()
    {
        $payload = array();
        
        $lookup = Application::createLookupItems();
        $items = $lookup->getItems();
        foreach($items as $item) {
            $payload[] = $item->toArray();
        }
        
        $this->sendResponse($payload);
    }
}