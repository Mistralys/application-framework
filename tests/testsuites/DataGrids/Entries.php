<?php

declare(strict_types=1);

final class DataGrids_EntriesTest extends ApplicationTestCase
{
    /**
     * @var UI
     */
    private $ui;

    protected function setUp(): void
    {
        $this->ui = UI::getInstance();
    }

    public function test_countEntriesSimple() : void
    {
        $grid = $this->ui->createDataGrid('grid'.$this->getTestCounter());
        $grid->addColumn('name', 'Name');

        $entries = array(
            array(
                'name' => 'Otto'
            ),
            array(
                'name' => 'Muster'
            )
        );

        $grid->render($entries);

        $this->assertSame(2, $grid->countEntries());
    }

    /**
     * Entries that are marked as non countable must be
     * excluded from the entries count.
     */
    public function test_countEntriesNonCountable() : void
    {
        $grid = $this->ui->createDataGrid('grid'.$this->getTestCounter());
        $grid->addColumn('name', 'Name');

        $entries = array(
            array(
                'name' => 'Otto'
            ),
            $grid->createEntry(array(
                'name' => 'Master'
            ))->makeNonCountable()
        );

        $grid->render($entries);

        $this->assertSame(1, $grid->countEntries());
    }

    /**
     * Headings must never count as entries.
     */
    public function test_countEntriesHeaders() : void
    {
        $grid = $this->ui->createDataGrid('grid'.$this->getTestCounter());
        $grid->addColumn('name', 'Name');

        $entries = array(
            array(
                'name' => 'Otto'
            ),
            $grid->createHeadingEntry('Heading')
        );

        $grid->render($entries);

        $this->assertSame(1, $grid->countEntries());
    }
}
