<?php

declare(strict_types=1);

use AppUtils\Interface_Stringable;

class Application_FilterCriteria_Database_Join implements Interface_Stringable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|DBHelper_StatementBuilder
     */
    private $statement;

    /**
     * @var string[]
     */
    private $requiredJoins = array();

    /**
     * @var Application_FilterCriteria_Database
     */
    private $filters;

    public function __construct(Application_FilterCriteria_Database $filters, $statement, string $joinID='')
    {
        if(empty($joinID))
        {
            $joinID = $this->generateID($statement);
        }

        $this->filters = $filters;
        $this->id = $joinID;
        $this->statement = $statement;
    }

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @return string
     */
    private function generateID($statement) : string
    {
        if($statement instanceof DBHelper_StatementBuilder)
        {
            return md5($statement->getTemplate());
        }

        return md5($statement);
    }

    /**
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatement() : string
    {
        return (string)$this->statement;
    }

    public function requireJoin(string $joinID) : Application_FilterCriteria_Database_Join
    {
        if(!in_array($joinID, $this->requiredJoins))
        {
            $this->requiredJoins[] = $joinID;
        }

        return $this;
    }

    public function hasJoins() : bool
    {
        return !empty($this->requiredJoins);
    }

    public function hasDependentJoins() : bool
    {
        $all = $this->filters->getJoins(true);
        $id = $this->getID();

        foreach ($all as $join)
        {
            if(in_array($id, $join->getRequiredJoinIDs()))
            {
                return true;
            }
        }

        return false;
    }


    /**
     * Retrieves all joins that depend on this one.
     * Includes joins that have only been registered,
     * and not yet added (they will be if this join
     * is used).
     *
     * @return Application_FilterCriteria_Database_Join[]
     * @throws DBHelper_Exception
     */
    public function getDependentJoins() : array
    {
        $all = $this->filters->getJoins(true);
        $id = $this->getID();
        $result = array();

        foreach ($all as $join)
        {
            if(in_array($id, $join->getRequiredJoinIDs()))
            {
                $result[] = $join;
            }
        }

        return $result;
    }

    /**
     * @return string[]
     * @throws DBHelper_Exception
     */
    public function getDependentJoinIDs() : array
    {
        $dependent = $this->getDependentJoins();
        $result = array();

        foreach($dependent as $join)
        {
            $result[] = $join->getID();
        }

        return $result;
    }

    /**
     * @return string[]
     * @throws DBHelper_Exception
     */
    public function getRequiredJoinIDs() : array
    {
        $result = array();
        $parents = $this->getParentJoins();

        foreach($parents as $parent)
        {
            $result[] = $parent->getID();
        }

        return $result;
    }

    public function __toString()
    {
        return $this->getStatement();
    }

    /**
     * Checks whether the specified join depends on this
     * join to be present (be it directly or indirectly).
     *
     * @param Application_FilterCriteria_Database_Join $join
     * @return bool
     * @throws DBHelper_Exception
     */
    public function dependsOn(Application_FilterCriteria_Database_Join $join) : bool
    {
        if(!$this->hasJoins())
        {
            return false;
        }

        $parents = $this->getParentJoins();
        $joinID = $join->getID();

        foreach($parents as $parent)
        {
            if($parent->getID() === $joinID)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Application_FilterCriteria_Database_Join[]
     * @throws DBHelper_Exception
     */
    public function getParentJoins() : array
    {
        $result = array();

        foreach($this->requiredJoins as $joinID)
        {
            $join = $this->filters->getJoinByID($joinID);

            $result[] = $join;

            if($join->hasJoins())
            {
                $result = array_merge($result, $join->getParentJoins());
            }
        }

        return $result;
    }
}
