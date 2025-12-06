<?php

declare(strict_types=1);

use AppFrameworkTestClasses\ApplicationTestCase;

final class DataGrids_SelectColumnsTest extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }

    public function test_hide_defaultUser() : void
    {
        $grid = $this->createDataGrid();

        $column = $grid->addColumn('col1', 'Column 1');

        $this->assertFalse($column->isHidden());
        $this->assertFalse($column->isHiddenForUser());

        $column->setHiddenForUser(true);

        $this->assertTrue($column->isHidden());
        $this->assertTrue($column->isHiddenForUser());
    }

    public function test_hide_specificUser() : void
    {
        $grid = $this->createDataGrid();
        $user = Application::createDummyUser();

        $this->assertNotEquals(Application::getUser()->getID(), $user->getID());

        $column = $grid->addColumn('col1', 'Column 1');

        $this->assertFalse($column->isHidden());
        $this->assertFalse($column->isHiddenForUser($user));

        $column->setHiddenForUser(true, $user);

        $this->assertFalse($column->isHidden());
        $this->assertTrue($column->isHiddenForUser($user));
    }

    /**
     * @return UI_DataGrid
     * @throws UI_Exception
     */
    private function createDataGrid() : UI_DataGrid
    {
        return UI::getInstance()
            ->createDataGrid('test' . $this->getTestCounter('datagrid'));
    }
}
