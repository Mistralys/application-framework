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
     * @var string
     */
    private $statement;

    public function __construct(string $statement, string $joinID='')
    {
        if(empty($joinID))
        {
            $joinID = md5($statement);
        }

        $this->id = $joinID;
        $this->statement = $statement;
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
        return $this->statement;
    }

    public function __toString()
    {
        return $this->statement;
    }
}
