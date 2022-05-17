<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\DBHelper;

use DBHelper_StatementBuilder;
use DBHelperTestCase;
use function statementBuilder;
use function statementValues;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class StatementBuilderTests extends DBHelperTestCase
{
    public function test_tableName() : void
    {
        $this->assertSame(
            'SELECT * FROM `table_name`',
            (string)statementBuilder('SELECT * FROM {table}')
                ->table('table', 'table_name')
        );
    }

    public function test_rawValue() : void
    {
        $this->assertSame(
            'raw-value',
            (string)statementBuilder('{raw}')
                ->val('raw', 'raw-value')
        );
    }

    public function test_tableAlias() : void
    {
        $this->assertSame(
            'SELECT * FROM `table_name` AS `table_alias`',
            (string)statementBuilder('SELECT * FROM `table_name` AS {alias}')
                ->alias('alias', 'table_alias')
        );
    }

    public function test_field() : void
    {
        $this->assertSame(
            'SELECT `field` FROM `table_name`',
            (string)statementBuilder('SELECT {field_name} FROM `table_name`')
                ->field('field_name', 'field')
        );
    }

    public function test_int() : void
    {
        $this->assertSame(
            'WHERE `field` = 42',
            (string)statementBuilder('WHERE `field` = {value}')
                ->int('value', 42)
        );
    }

    public function test_unknownPlaceholder() : void
    {
        $this->expectExceptionCode(DBHelper_StatementBuilder::ERROR_PLACEHOLDER_NOT_FOUND);

        statementBuilder("{placeholder-1}")
            ->field('placeholder-2', 'value');
    }

    public function test_missingPlaceholders() : void
    {
        $this->expectExceptionCode(DBHelper_StatementBuilder::ERROR_UNFILLED_PLACEHOLDER_DETECTED);

        statementBuilder("{placeholder-1} {placeholder-2}")
            ->field('placeholder-1', 'value')
            ->render();
    }

    public function test_duplicatePlaceholders() : void
    {
        $this->assertSame(
            '`name` `name`',
            (string)statementBuilder('{table_name} {table_name}')
                ->table('table_name', 'name')
        );
    }

    public function test_container() : void
    {
        $result = (string)statementValues()
            ->field('container_placeholder', 'container')
            ->statement(
                "{internal_placeholder} {container_placeholder}"
            )
            ->field('internal_placeholder', 'internal');

        $this->assertEquals('`internal` `container`', $result);
    }

    public function test_caseSensitivity() : void
    {
        $result = (string)statementBuilder("{recognized} {NOTRECOGNIZED}")
            ->field('recognized', 'OK');

        $this->assertEquals('`OK` {NOTRECOGNIZED}', $result);
    }

    public function test_placeholderName() : void
    {
        $this->assertEquals(
            '`found`',
            (string)statementBuilder('{recognized}')
                ->field('{recognized}', 'found')
        );
    }
}
