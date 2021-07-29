<?php
/**
 * File containing the class {@see Application_FilterCriteria_Database_CustomColumn}.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @see Application_FilterCriteria_Database_CustomColumn
 */

declare(strict_types=1);

use AppUtils\NamedClosure;

/**
 * Container for the configuration of individual custom columns
 * to use in a database filter criteria class.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_FilterCriteria_Database::registerCustomColumn()
 */
class Application_FilterCriteria_Database_CustomColumn
{
    const ERROR_SELECT_STATEMENT_NOT_A_STRING = 90401;

    /**
     * @var NamedClosure
     */
    private $callback;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Application_FilterCriteria_Database
     */
    private $filters;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var string[]
     */
    private $joins = array();

    public function __construct(Application_FilterCriteria_Database $filters, string $name, NamedClosure $callback)
    {
        $this->filters = $filters;
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function addJOIN(string $statement) : Application_FilterCriteria_Database_CustomColumn
    {
        $this->joins[] = $statement;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getJOINs() : array
    {
        return $this->joins;
    }

    public function hasJOINs() : bool
    {
        return !empty($this->joins);
    }

    public function setEnabled(bool $enabled) : Application_FilterCriteria_Database_CustomColumn
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database_CustomColumn::ERROR_SELECT_STATEMENT_NOT_A_STRING
     */
    public function getSelect() : string
    {
        $result = call_user_func($this->callback);

        if(is_string($result)) {
            return $result.' AS '.$this->name;
        }

        throw new Application_Exception(
            'Invalid custom column select value',
            sprintf(
                'The callback for the custom column [%s] did not return a string, but [%s].',
                $this->name,
                gettype($result)
            ),
            self::ERROR_SELECT_STATEMENT_NOT_A_STRING
        );
    }
}
