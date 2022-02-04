<?php

declare(strict_types=1);

abstract class Application_RequestLog_FileFilterCriteria_FileMatcher
{
    /**
     * @var callable
     */
    protected $valueCallback;

    /**
     * @var string
     */
    private $criteriaName;

    public function __construct(string $criteriaName, callable $valueCallback)
    {
        $this->valueCallback = $valueCallback;
        $this->criteriaName = $criteriaName;
    }

    /**
     * @return string
     */
    public function getCriteriaName() : string
    {
        return $this->criteriaName;
    }

    abstract public function isMatch(Application_RequestLog_LogFile $file, string $search) : bool;
}
