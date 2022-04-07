<?php
/**
 * File containing the class {@see DBHelper_StatementBuilder}.
 *
 * @package DBHelper
 * @subpackage StatementBuilder
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
 * @subpackage StatementBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_StatementBuilder extends DBHelper_StatementBuilder_ValuesContainer implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const ERROR_PLACEHOLDER_NOT_FOUND = 94101;
    public const ERROR_UNFILLED_PLACEHOLDER_DETECTED = 94102;

    /**
     * @var string
     */
    private string $template;

    public function __construct(string $statementTemplate)
    {
        $this->template = $statementTemplate;
    }

    public function getTemplate() : string
    {
        return $this->template;
    }

    /**
     * @param string $placeholderName
     * @param string $value
     * @param int $valueType
     * @return $this
     *
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    protected function add(string $placeholderName, string $value, int $valueType) : self
    {
        $this->requirePlaceholderExists($placeholderName);

        return parent::add($placeholderName, $value, $valueType);
    }

    /**
     * @param string $placeholder
     * @throws DBHelper_Exception
     * @see DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND
     */
    private function requirePlaceholderExists(string $placeholder) : void
    {
        if(strpos($this->template, $placeholder) !== false)
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
        $placeholders = $this->collectPlaceholderValues();

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->template
        );
    }

    /**
     * @return array<string,string>
     * @throws DBHelper_Exception
     */
    private function collectPlaceholderValues() : array
    {
        $this->analyzePlaceholders();

        $result = array();

        $placeholders = self::detectPlaceholderNames($this->template);

        foreach($placeholders as $placeholderName)
        {
            $result['{'.$placeholderName.'}'] = $this->getValue($placeholderName);
        }

        return $result;
    }

    /**
     * @param string $subject
     * @return string[]
     */
    public static function detectPlaceholderNames(string $subject) : array
    {
        preg_match_all('/{([a-z0-9_-]+)}/U', $subject, $result);
        return $result[1];
    }

    /**
     * @return string[]
     */
    private function detectMissingPlaceholders() : array
    {
        $names = self::detectPlaceholderNames($this->template);
        $missing = array();

        foreach($names as $placeholderName)
        {
            if(!$this->hasValue($placeholderName))
            {
                $missing[] = $placeholderName;
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
            'Unfilled placeholder in statement.'.
            sprintf(
                'The placeholders %1$s have not been filled.'.PHP_EOL.
                'Statement:'.PHP_EOL.
                '%2$s',
                '{'.implode('}, {', $missing).'}',
                $this->template
            ),
            '',
            self::ERROR_UNFILLED_PLACEHOLDER_DETECTED
        );
    }
}
