<?php

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ConvertHelper;

class Application_AjaxMethods_LookupItems extends Application_AjaxMethod
{
    public function processJSON() : void
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
    protected array $terms = array();
    
   /**
    * @var Application_LookupItems_Item[]
    */
    protected array $items = array();
    
    protected function validateRequest() : void
    {
        $lookup = AppFactory::createLookupItems();
        $items = $lookup->getItems();
        
        foreach($items as $item)
        {
            $id = $item->getID();
            $terms = (string)$this->request->getParam('terms_' . $id);

            if(empty($terms)) {
                continue;
            }

            $terms = $this->filterTerms($terms);

            $this->items[] = $item;
            $this->terms[] = ConvertHelper::explodeTrim(',', $terms);
        }
    }

    /**
     * @var array<string,string>
     */
    private array $replaceChars = array(
        '_' => ' ',
        '-' => ' ',
    );

    private function filterTerms(string $terms) : string
    {
        $terms = str_replace(
            array_keys($this->replaceChars),
            array_values($this->replaceChars),
            $terms
        );

        while(strpos($terms, '  ') !== false) {
            $terms = str_replace('  ', ' ', $terms);
        }

        return $terms;
    }
}
