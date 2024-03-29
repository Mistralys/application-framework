<?php

declare(strict_types=1);

use AppLocalize\Localization;
use AppFrameworkTestClasses\ApplicationTestCase;

final class DataGrids_EntriesTest extends ApplicationTestCase
{
    public function test_countEntriesSimple() : void
    {
        $grid = $this->createUI()->createDataGrid('grid'.$this->getTestCounter());
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
        $grid = $this->createUI()->createDataGrid('grid'.$this->getTestCounter());
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
        $grid = $this->createUI()->createDataGrid('grid'.$this->getTestCounter());
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

    public function test_defaultFooterCountText_EN() : void
    {
        Localization::selectAppLocale('de_DE');

        $grid = $this->createUI()->createDataGrid('grid'.$this->getTestCounter());
        $this->assertSame('Zeige Einträge 1 bis 2, 2 insgesamt.',$grid->getFooterCountText(1,2,2));

        Localization::selectAppLocale('en_UK');

        $this->assertSame('Showing entries 1 to 2, 2 total.',$grid->getFooterCountText(1,2,2));
    }

    public function test_defaultFooterCountText_DE() : void
    {
        Localization::selectAppLocale('de_DE');
        $grid = $this->createUI()->createDataGrid('grid'.$this->getTestCounter());
        $this->assertSame('Zeige Einträge 1 bis 2, 2 insgesamt.',$grid->getFooterCountText(1,2,2));
    }

    public function test_customFooterCountText_EN() : void
    {
        Localization::selectAppLocale('en_UK');
        $grid = $this->createUI()->createDataGrid('grid'.$this->getTestCounter());
        $grid->setFooterCountText(t('Showing communication types %1$s to %2$s, %3$s total.', '[FROM]', '[TO]', '[TOTAL]'));
        $this->assertSame('Showing communication types 1 to 2, 2 total.',$grid->getFooterCountText(1,2,2));
    }
}
