<?php

declare(strict_types=1);

final class DBHelper_StatementBuilderTests extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }

    public function test_tableName() : void
    {
        $this->assertSame(
            'SELECT * FROM `table_name`',
            (string)statementBuilder('SELECT * FROM {table}')
                ->table('table', 'table_name')
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
            ->add('placeholder-2', 'value');
    }

    public function test_missingPlaceholders() : void
    {
        $this->expectExceptionCode(DBHelper_StatementBuilder::ERROR_UNFILLED_PLACEHOLDER_DETECTED);

        statementBuilder("{placeholder-1} {placeholder-2}")
            ->add('placeholder-1', 'value')
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
}
