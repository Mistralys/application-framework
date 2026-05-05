<?php

declare(strict_types=1);

namespace DBHelper\Exception;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\StringBuilder;
use AppUtils\Traits\RenderableTrait;
use DBHelper;
use PDOException;

abstract class BaseErrorRenderer implements RenderableInterface
{
    use RenderableTrait;

    protected StringBuilder $message;
    protected ?PDOException $exception;

    public function __construct(?PDOException $exception, ?string $message)
    {
        $this->exception = $exception;
        $this->message = sb();

        if(empty($message)) {
            $message = DBHelper::getErrorMessage();
        }

        if(empty($message)) {
            $message = $this->getEmptyMessageText();
        }

        $this->line($message);

        $this->line('Database: '.$this->resolveConnectionInfo());

        if(isset($this->exception)) {
            $this->line('PDO exception type: '.get_class($this->exception));
            $this->line('PDO message: '.$this->exception->getMessage());
        }

        $query = DBHelper::getActiveQuery();

        if($query !== null)
        {
            $this->dumpSQL();
            $this->analyzeQuery($query[0], $query[1]);
        }
    }

    /**
     * Resolves the active database connection info as a human-readable string
     * in the form <code>username@name on host</code>.
     *
     * The primary path calls {@see DBHelper::getSelectedDB()} so that the
     * correct database is reported even when a non-default database (e.g. the
     * test database) is selected at the time of the error.
     *
     * If {@see DBHelper::getSelectedDB()} throws — which can happen during
     * early boot before any database has been selected — the method falls back
     * to the boot-time constants <code>APP_DB_USER</code>,
     * <code>APP_DB_NAME</code>, and <code>APP_DB_HOST</code>.
     * <code>\Throwable</code> is caught (rather than a narrower type) to guard
     * against both <code>DBHelper_Exception</code> and any unexpected
     * <code>Error</code> subclass that could surface inside the try block.
     *
     * @return string Connection info string, e.g. <code>app_user@mydb on localhost</code>.
     */
    private function resolveConnectionInfo(): string
    {
        try
        {
            $db = DBHelper::getSelectedDB();
            return $db['username'] . '@' . $db['name'] . ' on ' . $db['host'];
        }
        catch(\Throwable $e)
        {
            // Fallback to boot-time constants if no DB is selected yet
            return APP_DB_USER . '@' . APP_DB_NAME . ' on ' . APP_DB_HOST;
        }
    }

    private function dumpSQL() : void
    {
        $this->nl();
        $this->line('SQL (with simulated variable values):');
        $this->line($this->renderSQL());
    }

    abstract protected function renderSQL() : string;

    abstract public function getEmptyMessageText() : string;

    abstract protected function nl() : self;

    abstract protected function line(string $content) : self;

    abstract protected function styleError(string $text) : string;

    /**
     * @param array<string,mixed> $values
     */
    private function analyzeQuery(string $sql, array $values) : void
    {
        $this->nl();
        $this->line('Query placeholders:');

        // retrieve a list of all placeholders used in the query
        $params = array();
        $paramNames = array();
        preg_match_all('/[:]([a-zA-Z0-9_]+)/', $sql, $params, PREG_PATTERN_ORDER);

        if(isset($params[1][0])) {
            $paramNames = array_unique($params[1]);
        }

        if(empty($paramNames) && empty($values))
        {
            $this->line('(none)');
            return;
        }

        $tokens = array();
        $errors = false;
        foreach($paramNames as $name)
        {
            $foundName = null;
            if(array_key_exists($name, $values)) {
                $foundName = $name;
            }

            if(array_key_exists(':'.$name, $values)) {
                $foundName = ':'.$name;
            }

            if($foundName) {
                $tokenInfo = JSONConverter::var2json($values[$foundName], JSON_THROW_ON_ERROR);
            } else {
                $errors = true;
                $tokenInfo = $this->styleError('Placeholder has not been specified in the value list');
            }

            $tokens[] = $name . ' = ' . $tokenInfo;
        }

        foreach($values as $name => $value) {
            if(!in_array(ltrim($name, ':'), $paramNames, true)) {
                $errors = true;
                $tokens[] = $name . ' = '.$this->styleError('No matching placeholder found in the query');
            }
        }

        if($errors) {
            $this->line('NOTE: Placeholders have inconsistencies.');
        }

        foreach($tokens as $token) {
            $this->line($token);
        }
    }

    public function render(): string
    {
        return (string)$this->message;
    }
}
