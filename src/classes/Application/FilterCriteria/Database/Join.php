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

    public function __construct($statement, string $joinID='')
    {
        if(empty($joinID))
        {
            $joinID = $this->generateID($statement);
        }

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

    public function getRequiredJoinIDs() : array
    {
        return $this->requiredJoins;
    }

    public function __toString()
    {
        return $this->getStatement();
    }
}
