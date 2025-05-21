<?php
/**
 * @package DBHelper
 */

declare(strict_types=1);

namespace DBHelper;

use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\Highlighter;
use AppUtils\ThrowableInfo\ThrowableCall;
use DBHelper;
use DBHelper_Exception;
use DBHelper_OperationTypes;
use DBHelper_StatementBuilder;
use function AppUtils\parseThrowable;

/**
 * Utility class that holds information on a single query
 * executed during this request. See {@see DBHelper::getQueries()}.
 *
 * @package DBHelper
 */
class TrackedQuery
{
    /**
     * @var ThrowableCall[]
     */
    private array $backtrace;
    private int $operationTypeID;
    private float $duration;
    /**
     * @var array<string,string>
     */
    private array $variables;
    /**
     * @var DBHelper_StatementBuilder|string
     */
    private $statement;

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @param array<string,string> $variables
     * @param float $duration
     * @param int $operationTypeID
     */
    public function __construct($statement, array $variables, float $duration, int $operationTypeID)
    {
        $this->statement = $statement;
        $this->variables = $variables;
        $this->duration = $duration;
        $this->operationTypeID = $operationTypeID;
        $this->backtrace = parseThrowable(new DBHelper_Exception('Stub'))->getCalls();
    }

    /**
     * @return ThrowableCall[]
     */
    public function getFullTrace(): array
    {
        return $this->backtrace;
    }

    public function getOperationTypeID(): int
    {
        return $this->operationTypeID;
    }

    public function isSelect() : bool
    {
        return $this->operationTypeID === DBHelper_OperationTypes::TYPE_SELECT;
    }

    public function isDelete() : bool
    {
        return $this->operationTypeID === DBHelper_OperationTypes::TYPE_DELETE;
    }

    public function isInsert() : bool
    {
        return $this->operationTypeID === DBHelper_OperationTypes::TYPE_INSERT;
    }

    public function isUpdate() : bool
    {
        return $this->operationTypeID === DBHelper_OperationTypes::TYPE_UPDATE;
    }

    public function isWriteOperation() : bool
    {
        return DBHelper_OperationTypes::isWriteOperation($this->operationTypeID);
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return array<string,string>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return DBHelper_StatementBuilder|string
     */
    public function getStatement()
    {
        return $this->statement;
    }

    public function getSQLFormatted() : string
    {
        return ConvertHelper::normalizeTabs(DBHelper::formatQuery((string)$this->getStatement(), $this->getVariables()));
    }

    public function getSQLHighlighted() : string
    {
        return Highlighter::sql($this->getSQLFormatted());
    }

    /**
     * Gets the most likely function call from which the query originated.
     * @return ThrowableCall|null
     */
    public function getOriginator() : ?ThrowableCall
    {
        $calls = $this->getFilteredTrace();
        if(!empty($calls)) {
            return array_pop($calls);
        }

        return null;
    }

    /**
     * Gets all calls in the trace that are not part of the DBHelper package.
     * @return ThrowableCall[]
     */
    public function getFilteredTrace() : array
    {
        $backtrace = $this->getFullTrace();

        $skipFiles = array(
            FileHelper::getFilename(__FILE__)
        );
        $skipClasses = array(
            ltrim(self::class, '\\'),
            ltrim(DBHelper::class, '\\')
        );

        $calls = array();
        foreach($backtrace as $call) {
            if(
                in_array($call->getFileName(), $skipFiles, true)
                ||
                in_array(ltrim($call->getClass(), '\\'), $skipClasses, true)
            ) {
                continue;
            }

            $calls[] = $call;
        }

        return $calls;
    }

    /**
     * Gets a string representation of the call trace that led to this query.
     * @return string
     */
    public function trace2string() : string
    {
        $output = '';
        $pos = 1;
        foreach($this->getFilteredTrace() as $call) {
            $output .= $this->call2string($call, $pos);
            $pos++;
        }

        return $output;
    }

    private function call2string(ThrowableCall $call, int $pos) : string
    {
        $tokens = array();

        $padLength = 2;

        $tokens[] = '#'.sprintf('%0'.$padLength.'d', $pos).' ';

        if($call->hasFile()) {
            $tokens[] = $call->getFileRelative().':'.$call->getLine();
        }

        if($call->hasClass()) {
            $tokens[] = $call->getClass().'::'.$call->getFunction().'()';
        } else if($call->hasFunction()) {
            $tokens[] = $call->getFunction().'()';
        }

        return implode(' ', $tokens).PHP_EOL;
    }
}
