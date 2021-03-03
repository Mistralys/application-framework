<?php

use function AppUtils\parseVariable;

abstract class Application_FilterCriteria implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    const ERROR_INVALID_SORTING_ORDER = 710003;
    const ERROR_NON_SCALAR_CRITERIA_VALUE = 710005;

    const MESSAGE_TYPE_INFO = 'info';
    const MESSAGE_TYPE_WARNING = 'warning';

    /**
     * @var string|NULL
     */
    protected $orderField = null;

    /**
     * @var string
     */
    protected $orderDir = 'ASC';

    /**
     * @var string
     */
    protected $search = '';
    
    /**
     * The offset to start retrieving from
     * @var int
     */
    protected $offset = 0;
    
    /**
     * The limit of entries to fetch
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int|NULL
     */
    protected $totalUnfiltered = null;

    /**
     * @var bool
     */
    protected $isCount = false;

    /**
     * @var bool
     */
    protected $dumpQuery = false;

    /**
     * @var array<array<string,string>>
     */
    protected $messages = array();

    /**
     * @var array<string,mixed[]>
     */
    protected $criteriaItems = array();

    /**
     * @var array<string,string>
     */
    protected $connectors;

    /**
    * Sets the sorting order to ascending.
    * @return $this
    */
    public function orderAscending()
    {
        $this->orderDir = 'ASC';
        return $this;
    }
    
   /**
    * Sets the sorting order to descending.
    * @return $this
    */
    public function orderDescending()
    {
        $this->orderDir = 'DESC';
        return $this;
    }
    
   /**
    * Sets the search terms string.
    * 
    * @param string $search
    * @return $this
    */
    public function setSearch(string $search)
    {
        $search = trim($search);
        if(!empty($search)) {
            $this->search = $search;
        }
        
        return $this;
    }
    
    /**
     * Sets the limit for the list.
     * 
     * @param int $offset
     * @param int $limit
     * @return $this
     */
    public function setLimit($offset = 0, $limit = 0)
    {
        $this->offset = intval($offset);
        $this->limit = intval($limit);
        return $this;
    }
    
    /**
     * Sets the limit for the list by using an existing data
     * grid object, which contains the current pagination details.
     *
     * @param UI_DataGrid $datagrid
     * @return $this
     */
    public function setLimitFromDatagrid(UI_DataGrid $datagrid)
    {
        $this->setLimit(
            $datagrid->getLimit(),
            $datagrid->getOffset()
        );
        
        return $this;
    }

    // region: Abstract methods
    
   /**
    * Counts the amount of matching records, 
    * according to the current filter criteria.
    * 
    * NOTE: use the <code>countUnfiltered()</code>
    * method to count all records, without matching
    * the current criteria.
    * 
    * @return int
    * @see Application_FilterCriteria::countUnfiltered()
    */
    abstract public function countItems() : int;

    /**
     * Retrieves all matching items as an indexed array containing
     * associative array entries with the item data.
     *
     * @return array
     * @throws Application_Exception
     * @see getItem()
     */
    abstract public function getItems();

    // endregion

    /**
     * Counts the total, unfiltered amount of entries.
     * @return integer
     */
    public final function countUnfiltered() : int
    {
        if(isset($this->totalUnfiltered)) {
            return $this->totalUnfiltered;
        }
        
        $pristine = $this->createPristine();
        $this->totalUnfiltered = $pristine->countItems();

        return $this->totalUnfiltered;
    }
    
    /**
     * Creates a pristine filter instance that uses the
     * default filtering settings. This is used among
     * other things to count the total amount of records
     * when not using any filters.
     *
     * @return Application_FilterCriteria
     */
    protected function createPristine()
    {
        $class = get_class($this);
        return new $class();
    }
    
    protected function getConnector($searchTerm)
    {
        if (!isset($this->connectors)) {
            $this->connectors = array(
                'AND' => t('AND'),
                'OR' => t('OR')
            );
        }
        
        foreach ($this->connectors as $connector => $translation) {
            if ($searchTerm === $connector || $searchTerm === $translation) {
                return $connector;
            }
        }
        
        return null;
    }
    
    /**
     * Whether to dump the final filter query. Can be used in the
     * getQuery method to show the resulting query in the UI.
     *
     * @param boolean $debug
     * @return $this
     */
    public function debugQuery(bool $debug=true)
    {
        $this->dumpQuery = $debug;
        return $this;
    }
    
    /**
     * Sets the field to order the results by.
     *
     * @param string $fieldName
     * @param string $orderDir
     * @throws Application_Exception
     * @return $this
     */
    public function setOrderBy(string $fieldName, string $orderDir = 'ASC')
    {
        $this->orderField = $fieldName;
        $this->orderDir = $this->requireValidOrderDir($orderDir);
        
        return $this;
    }

    /**
     * @param string $orderDir
     * @return string
     * @throws Application_Exception
     */
    protected function requireValidOrderDir(string $orderDir) : string
    {
        $orderDir = strtoupper($orderDir);

        if ($orderDir === 'ASC' || $orderDir === 'DESC') {
            return $orderDir;
        }

        throw new Application_Exception(
            'Invalid sorting order',
            sprintf(
                'The sorting order [%1$s] is not a valid order string.',
                $orderDir
            ),
            self::ERROR_INVALID_SORTING_ORDER
        );
    }

    /**
     * @return string[]
     */
    public function getSearchTerms() : array
    {
        if (empty($this->search)) {
            return array();
        }
        
        // search for strings that are to be treated as literals:
        // all quoted string pairs. These get replaced by placeholders
        // and restored as-is after the string size check.
        $search = $this->search;
        $tokens = array();
        $literals = array();
        preg_match_all('/"([^"]+)"/i', $search, $tokens, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($tokens[0]); $i++) {
            $replace = '_LIT'.$i.'_';
            $search = str_replace($tokens[$i][0], $replace, $search);
            $literals[$replace] = $tokens[($i+1)][0];
        }
        
        $minLength = 2;
        $terms = explode(' ', $search);
        $terms = array_map('trim', $terms);
        
        $result = array();
        foreach ($terms as $term) {
            if($this->getConnector($term)) {
                $result[] = $term;
                continue;
            }
            
            if (strlen($term) < $minLength) {
                $this->addInfo(t(
                    'The term %1$s was ignored, search terms must be at least %2$s characters long.',
                    '"'.$term.'"',
                    $minLength
                ));
                continue;
            }
            
            // restore literals
            if(isset($literals[$term])) {
                $term = $literals[$term];
            }
            
            $result[] = $term;
        }
        
        return $result;
    }

    /**
     * Configures both the data grid and the filters using
     * the current settings, from the limit to the column
     * to order by and the order direction.
     *
     * @param UI_DataGrid $grid
     * @return $this
     * @throws Application_Exception
     */
    public function configure(UI_DataGrid $grid)
    {
        $total = $this->countItems();
        
        $grid->setTotal($total);
        $this->setLimitFromDatagrid($grid);
        
        // does the datagrid have a specific order column,
        // and if yes, can it be sorted via query? If it has
        // a sorting callback, we have to let it order manually.
        $column = $grid->getOrderColumn();
        if(!$column || $column->hasSortingCallback()) {
            return $this;
        }
        
        $this->setOrderBy(
            $grid->getOrderColumn()->getOrderKey(),
            $grid->getOrderDir()
        );

        return $this;
    }

    /**
     * Adds an information message.
     *
     * @param string $message
     * @return $this
     */
    public function addInfo(string $message)
    {
        return $this->addMessage($message, self::MESSAGE_TYPE_INFO);
    }

    /**
     * Adds a warning message.
     *
     * @param string $message
     * @return $this
     */
    public function addWarning(string $message)
    {
        return $this->addMessage($message, self::MESSAGE_TYPE_WARNING);
    }

    /**
     * @param string $message
     * @param string $type
     * @return $this
     */
    protected function addMessage(string $message, string $type)
    {
        $this->messages[] = array(
            'message' => $message,
            'type' => $type
        );
        
        return $this;
    }
    
    public function hasMessages() : bool
    {
        return !empty($this->messages);
    }
    
    public function getMessages() : array
    {
        return $this->messages;
    }

    /**
     * @return $this
     */
    public function resetMessages()
    {
        $this->messages = array();
        return $this;
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'FilterCriteria [%s]',
            getClassTypeName($this)
        );
    }

    /**
     * Helper method: when selecting custom criteria to limit a
     * query, this can be used as a generic method to store values.
     *
     * Example: Selecting specific products to limit a list to.
     *
     * <pre>
     * public function selectProduct(Product $product)
     * {
     *     return $this->selectCriteriaValue('product', $product->getID());
     * }
     * </pre>
     *
     * @param string $type
     * @param mixed $value
     * @return $this
     * @throws Application_Exception
     * @see selectCriteriaValues()
     */
    protected function selectCriteriaValue(string $type, $value)
    {
        if(empty($value)) {
            return $this;
        }
        
        if(!is_scalar($value)) 
        {
            throw new Application_Exception(
                'Invalid criteria value.',
                sprintf(
                    'Non-scalar values are not allowed as criteria values for criteria [%s], [%s] given.',
                    $type,
                    parseVariable($value)->enableType()->toString()
                ),
                self::ERROR_NON_SCALAR_CRITERIA_VALUE
            );
        }
        
        if(!isset($this->criteriaItems[$type])) {
            $this->criteriaItems[$type] = array();
        }
        
        if(!in_array($value, $this->criteriaItems[$type])) {
            $this->criteriaItems[$type][] = $value;
        }
        
        return $this;
    }

    /**
     * Selects several values at once.
     *
     * @param string $type
     * @param array $values
     * @return $this
     * @throws Application_Exception
     * @see selectCriteriaValue()
     */
    protected function selectCriteriaValues(string $type, array $values)
    {
        if(!empty($values)) {
            foreach($values as $value) {
                $this->selectCriteriaValue($type, $value);
            }
        }
        
        return $this;
    }

    /**
     * @param string $type
     * @return array<scalar>
     */
    protected function getCriteriaValues(string $type) : array
    {
        if(isset($this->criteriaItems[$type])) {
            return $this->criteriaItems[$type];
        }
        
        return array();
    }

    /**
     * Whether any values have been added for the specified type.
     *
     * @param string $type
     * @return bool
     */
    protected function hasCriteriaValues(string $type) : bool
    {
        return isset($this->criteriaItems[$type]) && !empty($this->criteriaItems[$type]);
    }
}
