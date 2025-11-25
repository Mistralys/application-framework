<?php
/**
 * @package Application
 * @subpackage FilterCriteria
 */

declare(strict_types=1);

use Application\FilterCriteria\Events\ApplyFiltersEvent;
use Application\FilterCriteria\FilterCriteriaException;
use Application\Interfaces\FilterCriteriaInterface;
use AppUtils\BaseException;
use AppUtils\OperationResult;
use AppUtils\OperationResult_Collection;
use UI\PaginationRenderer;
use function AppUtils\parseVariable;

/**
 * @package Application
 * @subpackage FilterCriteria
 * @see FilterCriteriaInterface
 */
abstract class Application_FilterCriteria
    implements
    Application_Interfaces_Loggable,
    Application_Interfaces_Instanceable,
    Application_Interfaces_Eventable,
    FilterCriteriaInterface
{
    use Application_Traits_Loggable;
    use Application_Traits_Instanceable;
    use Application_Traits_Eventable;

    public const ERROR_INVALID_SORTING_ORDER = 710003;
    public const ERROR_NON_SCALAR_CRITERIA_VALUE = 710005;

    public const EVENT_APPLY_FILTERS = 'ApplyFilters';

    protected ?string $orderField = null;
    protected string $orderDir = FilterCriteriaInterface::ORDER_DIR_ASCENDING;
    protected string $search = '';
    protected int $offset = 0;
    protected int $limit = 0;
    protected ?int $totalUnfiltered = null;
    protected bool $isCount = false;
    protected bool $dumpQuery = false;

    protected OperationResult_Collection $messages;

    /**
     * @var array<string,mixed[]>
     */
    protected array $criteriaItems = array();

    /**
     * @var array<string,string>
     */
    protected array $connectors;

    /**
     * @var array<int,mixed>
     */
    private array $constructorArguments;

    /**
     * The arguments are free and are stored internally:
     * When using the {@see Application_FilterCriteria::createPristine()}
     * method, the same arguments are passed on the new
     * instance.
     *
     * @see Application_FilterCriteria::createPristine()
     */
    public function __construct(...$args)
    {
        $this->constructorArguments = $args;
        $this->messages = new OperationResult_Collection($this);

        $this->init();
    }

    protected function init() : void {}

    public function orderAscending() : self
    {
        return $this->setOrderDir(FilterCriteriaInterface::ORDER_DIR_ASCENDING);
    }

    public function orderDescending() : self
    {
        return $this->setOrderDir(FilterCriteriaInterface::ORDER_DIR_DESCENDING);
    }

    public function setOrderDir(string $orderDir) : self
    {
        $this->requireValidOrderDir($orderDir);

        $this->orderDir = $orderDir;

        return $this;
    }

    public function setSearch(?string $search) : self
    {
        $search = trim((string)$search);

        if(!empty($search) && $this->search !== $search)
        {
            $this->search = $search;
            $this->handleCriteriaChanged();
        }
        
        return $this;
    }

    public function setLimitByPagination(PaginationRenderer $paginator) : self
    {
        $paginator->configureFilters($this);
        return $this;
    }

    public function setLimit(int $limit = 0, int $offset = 0) : self
    {
        $this->offset = $limit;
        $this->limit = $offset;

        return $this;
    }

    public function setLimitFromDatagrid(UI_DataGrid $datagrid) : self
    {
        $this->setLimit(
            $datagrid->getLimit(),
            $datagrid->getOffset()
        );
        
        return $this;
    }

    // region: Abstract methods

    abstract public function countItems() : int;

    abstract public function getItems() : array;

    // endregion

    final public function countUnfiltered() : int
    {
        if(isset($this->totalUnfiltered))
        {
            return $this->totalUnfiltered;
        }
        
        $pristine = $this->createPristine();
        $this->totalUnfiltered = $pristine->countItems();

        return $this->totalUnfiltered;
    }

    /**
     * Creates a pristine filter instance that uses the
     * default filtering settings. This is used among
     * other things to count the total number of records
     * when not using any filters.
     *
     * @return self
     */
    protected function createPristine() : self
    {
        $class = get_class($this);
        return new $class(...$this->constructorArguments);
    }
    
    protected function getConnector(string $searchTerm) : ?string
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
    public function debugQuery(bool $debug=true) : self
    {
        $this->dumpQuery = $debug;
        return $this;
    }

    public function setOrderBy(string $fieldName, string $orderDir = FilterCriteriaInterface::ORDER_DIR_ASCENDING) : self
    {
        if($this->orderField !== $fieldName)
        {
            $this->orderField = $fieldName;
            $this->handleCriteriaChanged();
        }

        $this->setOrderDir($orderDir);
        
        return $this;
    }

    public function getOrderField() : string
    {
        return (string)$this->orderField;
    }

    public function getOrderDir() : string
    {
        return $this->orderDir;
    }

    /**
     * @param string $orderDir
     * @return string
     * @throws Application_Exception
     * @see Application_FilterCriteria::ERROR_INVALID_SORTING_ORDER
     */
    protected function requireValidOrderDir(string $orderDir) : string
    {
        $orderDir = strtoupper($orderDir);

        if ($orderDir === FilterCriteriaInterface::ORDER_DIR_ASCENDING || $orderDir === FilterCriteriaInterface::ORDER_DIR_DESCENDING) {
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
        preg_match_all('/"([^"]+)"/', $search, $tokens, PREG_PATTERN_ORDER);
        for ($i = 0, $iMax = count($tokens[0]); $i < $iMax; $i++) {
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

    public function configure(UI_DataGrid $grid) : self
    {
        $total = $this->countItems();
        
        $grid->setTotal($total);
        $this->setLimitFromDatagrid($grid);
        
        // does the data grid have a specific order column,
        // and if yes, can it be sorted via a query?
        // If it has a sorting callback, we have to let it order manually.
        $column = $grid->getOrderColumn();
        if($column === null || $column->hasSortingCallback()) {
            return $this;
        }
        
        $this->setOrderBy(
            $column->getOrderKey(),
            $grid->getOrderDir()
        );

        return $this;
    }

    public function addInfo(string $message, int $code=0) : self
    {
        return $this->addMessage($message, FilterCriteriaInterface::MESSAGE_TYPE_INFO, $code);
    }

    public function addWarning(string $message, int $code=0) : self
    {
        return $this->addMessage($message, FilterCriteriaInterface::MESSAGE_TYPE_WARNING, $code);
    }

    public function addResultMessage(OperationResult $message) : self
    {
        $this->messages->addResult($message);
        return $this;
    }

    /**
     * @param string $message
     * @param string $type
     * @param int $code
     * @return $this
     */
    protected function addMessage(string $message, string $type, int $code=0) : self
    {
        if($type === FilterCriteriaInterface::MESSAGE_TYPE_INFO) {
            $this->messages->makeNotice($message, $code);
        } else if($type === FilterCriteriaInterface::MESSAGE_TYPE_WARNING) {
            $this->messages->makeWarning($message, $code);
        }

        return $this;
    }

    public function hasMessages() : bool
    {
        return $this->messages->countResults() > 0;
    }
    
    public function getMessages() : OperationResult_Collection
    {
        return $this->messages;
    }

    public function resetMessages() : self
    {
        $this->messages = new OperationResult_Collection($this);
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
     * @throws FilterCriteriaException
     * @throws BaseException
     * @see Application_FilterCriteria::selectCriteriaValues()
     *
     * @see Application_FilterCriteria::ERROR_NON_SCALAR_CRITERIA_VALUE
     */
    protected function selectCriteriaValue(string $type, $value) : self
    {
        if(empty($value)) {
            return $this;
        }
        
        if(!is_scalar($value)) 
        {
            throw new FilterCriteriaException(
                'Invalid criteria value.',
                sprintf(
                    'Non-scalar values are not allowed as criteria values for criteria [%s], [%s] given.',
                    $type,
                    parseVariable($value)->enableType()->toString()
                ),
                self::ERROR_NON_SCALAR_CRITERIA_VALUE
            );
        }
        
        if(!isset($this->criteriaItems[$type]))
        {
            $this->criteriaItems[$type] = array();
        }
        
        if(!in_array($value, $this->criteriaItems[$type], true))
        {
            $this->criteriaItems[$type][] = $value;
            $this->handleCriteriaChanged();
        }
        
        return $this;
    }

    /**
     * Selects several values at once.
     *
     * @param string $type
     * @param array<mixed> $values
     * @return $this
     * @throws BaseException
     * @throws FilterCriteriaException
     * @see self::selectCriteriaValue()
     */
    protected function selectCriteriaValues(string $type, array $values) : self
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
     * Whether any values have been added for the specified type(s).
     *
     * @param string ...$types
     * @return bool
     */
    protected function hasCriteriaValues(...$types) : bool
    {
        foreach($types as $type)
        {
            if(isset($this->criteriaItems[$type]) && !empty($this->criteriaItems[$type])) {
                return true;
            }
        }

        return false;
    }

    public function isCount() : bool
    {
        return $this->isCount;
    }

    // region: Applying the filters

    final public function applyFilters() : void
    {
        $this->_applyFilters();

        $this->triggerEvent(self::EVENT_APPLY_FILTERS, array($this), ApplyFiltersEvent::class);
    }

    public function onApplyFilters(callable $listener) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_APPLY_FILTERS, $listener);
    }

    protected function _applyFilters() : void {}

    // endregion

    /**
     * Called whenever a method is called that influences
     * the selection criteria: anything that modifies which
     * items are fetched.
     *
     * NOTE: This does not include settings like sorting order,
     * selection limits and the like, as they do not modify
     * the item filters.
     */
    protected function handleCriteriaChanged() : void
    {

    }
}
