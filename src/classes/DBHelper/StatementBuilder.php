<?php
/**
 * File containing the class {@see DBHelper_StatementBuilder}.
 *
 * @package DBHelper
 * @subpackage Helpers
 * @see DBHelper_StatementBuilder
 */

declare(strict_types=1);

/**
 * SQL statement builder utility, used to create statements
 * with human-readable placeholders for table names, field
 * names and the like.
 *
 * Usage:
 *
 * <pre>
 * $sql = (string)statementBuilder("SELECT * FROM {table_name} WHERE {field}=1")
 *     ->table('table_name', 'actual_table_name')
 *     ->field('field', 'field_name');
 * </pre>
 *
 * @package DBHelper
 * @subpackage Helpers
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_StatementBuilder implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    const ERROR_PLACEHOLDER_NOT_FOUND = 94101;
    const ERROR_UNFILLED_PLACEHOLDER_DETECTED = 94102;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array<string,string>
     */
    private $vars = array();

    public function __construct(string $statementTemplate)
    {
        $this->template = $statementTemplate;
    }

    private function wrapTicks(string $value) : string
    {
        return '`'.trim($value, '`').'`';
    }

    /**
     * @param string $tableName
     * @param string $value
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    public function table(string $tableName, string $value) : DBHelper_StatementBuilder
    {
        return $this->add($tableName, $this->wrapTicks($value));
    }

    /**
     * @param string $alias
     * @param string $value
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    public function alias(string $alias, string $value) : DBHelper_StatementBuilder
    {
        return $this->add($alias, $this->wrapTicks($value));
    }

    /**
     * @param string $name
     * @param int $value
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    public function int(string $name, int $value) : DBHelper_StatementBuilder
    {
        return $this->add($name, (string)$value);
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    public function field(string $fieldName, string $value) : DBHelper_StatementBuilder
    {
        return $this->add($fieldName, $this->wrapTicks($value));
    }

    /**
     * @param string $varName
     * @param string $value
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    public function add(string $varName, string $value) : DBHelper_StatementBuilder
    {
        $placeholder = '{'.$varName.'}';

        $this->requirePlaceholderExists($placeholder);

        $this->vars[$placeholder] = $value;
        return $this;
    }

    /**
     * @param string $placeholder
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    private function requirePlaceholderExists(string $placeholder) : void
    {
        if(strstr($this->template, $placeholder))
        {
            return;
        }

        throw new DBHelper_Exception(
            'Unknown placeholder in statement.',
            sprintf(
                'The placeholder %1$s was not found in the statement.'.PHP_EOL.
                'Note that placeholders are case sensitive.'.PHP_EOL.
                'Statement:'.PHP_EOL.
                '%2$s',
                $placeholder,
                $this->template
            ),
            self::ERROR_PLACEHOLDER_NOT_FOUND
        );
    }

    /**
     * @return string
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_UNFILLED_PLACEHOLDER_DETECTED
     */
    public function render() : string
    {
        $this->analyzePlaceholders();

        return str_replace(
            array_keys($this->vars),
            array_values($this->vars),
            $this->template
        );
    }

    /**
     * @param string $subject
     * @return string[]
     */
    public static function detectPlaceholders(string $subject) : array
    {
        preg_match_all('/{([a-zA-Z0-9_-]+)}/sU', $subject, $result, PREG_PATTERN_ORDER);
        return $result[0];
    }

    /**
     * @return string[]
     */
    private function detectMissingPlaceholders() : array
    {
        $placeholders = self::detectPlaceholders($this->template);

        $missing = array();

        foreach($placeholders as $placeholder)
        {
            if(!array_key_exists($placeholder, $this->vars))
            {
                $missing[] = $placeholder;
            }
        }

        return $missing;
    }

    /**
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_UNFILLED_PLACEHOLDER_DETECTED
     */
    private function analyzePlaceholders() : void
    {
        $missing = $this->detectMissingPlaceholders();

        if(empty($missing))
        {
            return;
        }

        throw new DBHelper_Exception(
            'Unfilled placeholder in statement.',
            sprintf(
                'The placeholders %1$s have not been filled.'.PHP_EOL.
                'Statement:'.PHP_EOL.
                '%2$s',
                implode(', ', $missing),
                $this->template
            ),
            self::ERROR_UNFILLED_PLACEHOLDER_DETECTED
        );
    }
}
