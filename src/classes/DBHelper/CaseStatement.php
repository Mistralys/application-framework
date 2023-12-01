<?php
/**
 * File containing the class {@see DBHelper_CaseStatement}.
 *
 * @package DBHelper
 * @see DBHelper_CaseStatement
 */

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;

/**
 * Helper class used to build `CASE` SQL statements.
 *
 * @package DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_CaseStatement implements StringableInterface
{
    /**
     * @var string
     */
    private $sourceColumn;

    /**
     * @var string[]
     */
    private $cases = array();

    /**
     * @param string|DBHelper_StatementBuilder $sourceColumn
     */
    public function __construct($sourceColumn)
    {
        $this->sourceColumn = (string)$sourceColumn;
    }

    /**
     * @param string|DBHelper_StatementBuilder $sourceColumn
     * @return DBHelper_CaseStatement
     */
    public static function create($sourceColumn) : DBHelper_CaseStatement
    {
        return new DBHelper_CaseStatement($sourceColumn);
    }

    public function addIntString(int $case, string $value) : DBHelper_CaseStatement
    {
        return $this->addCase((string)$case, $this->formatString($value));
    }

    public function addString(string $case, string $value) : DBHelper_CaseStatement
    {
        return $this->addCase($this->formatString($case), $this->formatString($value));
    }

    public function addInt(int $case, int $value) : DBHelper_CaseStatement
    {
        return $this->addCase((string)$case, (string)$value);
    }

    public function addStringInt(string $case, int $value) : DBHelper_CaseStatement
    {
        return $this->addCase($this->formatString($case), (string)$value);
    }

    private function addCase(string $case, string $value) : DBHelper_CaseStatement
    {
        $this->cases[] = sprintf(
            "  WHEN %s THEN %s",
            $case,
            $value
        );

        return $this;
    }

    private function formatString(string $string) : string
    {
        return sprintf("'%s'", addslashes($string));
    }

    /**
     * @return string
     */
    public function render() : string
    {
        $format = <<<EOT
CASE %s
%s
ELSE
  ''
END

EOT;

        return sprintf(
            $format,
            $this->sourceColumn,
            implode(PHP_EOL, $this->cases)
        );
    }

    public function __toString()
    {
        return $this->render();
    }
}
