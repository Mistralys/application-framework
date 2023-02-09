<?php

use Application\AppFactory;
use AppUtils\ConvertHelper;

class Application_AjaxMethods_LookupItems extends Application_AjaxMethod
{
    public function processJSON()
    {
        $payload = array();
        
        foreach($this->items as $idx => $item) 
        {
            $item->findMatches($this->terms[$idx]);
            
            $entries = array();
            $results = $item->getResults();
            foreach($results as $result) {
                $entries[] = $result->toArray();
            }

            $payload[$item->getID()] = $entries;
        }
        
        $this->sendResponse($payload);
    }
    
   /**
    * @var array<int,string[]>
    */
    protected $terms = array();
    
   /**
    * @var Application_LookupItems_Item[]
    */
    protected $items = array();
    
    protected function validateRequest() : void
    {
        $lookup = AppFactory::createLookupItems();
        $items = $lookup->getItems();
        
        foreach($items as $item)
        {
            $id = $item->getID();
            $terms = strval($this->request->getParam('terms_'.$id));

            if(empty($terms)) {
                continue;
            }

            $this->items[] = $item;
            $this->terms[] = ConvertHelper::explodeTrim(',', $terms);
        }
    }
}