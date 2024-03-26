<?php

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURL;

abstract class Application_LookupItems_Item
{
    /**
     * @return string
     */
    abstract public function getFieldLabel();

    /**
     * @return string
     */
    abstract public function getFieldDescription();

    /**
     * @param string[] $terms
     */
    abstract public function findMatches(array $terms);

    protected string $id;

    /**
     * @var Application_LookupItems_Result[]
     */
    protected array $results = array();

    public function getID() : string
    {
        if(!isset($this->id)) {
            $this->id = getClassTypeName($this);
        }
        
        return $this->id;
    }

    /**
     * @return array<string,string>
     */
    public function toArray() : array
    {
        return array(
            'id' => $this->getID(),
            'field_label' => $this->getFieldLabel(),
            'field_description' => $this->getFieldDescription()
        );
    }

    /**
     * @param string|number|StringableInterface $label
     * @param string|AdminURL $url
     * @return void
     * @throws UI_Exception
     */
    protected function addResult($label, $url) : void
    {
        $result = new Application_LookupItems_Result($this, $label, $url);
        $this->results[] = $result;
    }
    
   /**
    * @return Application_LookupItems_Result[]
    */
    public function getResults() : array
    {
        return $this->results;
    }

    /**
     * Retrieves a javascript statement to open the lookup
     * dialog for this item, with optional preset search
     * terms.
     *
     * The value must be a JS statement that evaluates to
     * a search terms string. Examples:
     *
     * <pre>
     * getJSSearch("'search terms'");
     * getJSSearch("$('#elementID').val()");
     * </pre>
     *
     * @param string $valueStatement
     * @return string
     */
    public function getJSSearch(string $valueStatement) : string
    {
        return sprintf(
            "Driver.DialogLookup('%s:' + %s);",
            $this->getID(),
            $valueStatement
        );
    }
}
