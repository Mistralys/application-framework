<?php

declare(strict_types=1);

namespace AppFrameworkTests\DataGrids;

use AppFrameworkTestClasses\Traits\DataGridTestTrait;
use AppLocalize\Localization;
use AppFrameworkTestClasses\ApplicationTestCase;
use DateTime;

final class EntryTests extends ApplicationTestCase
{
    use DataGridTestTrait;

    public function test_countEntriesSimple(): void
    {
        $grid = $this->createDataGrid();
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
    public function test_countEntriesNonCountable(): void
    {
        $grid = $this->createDataGrid();
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
    public function test_countEntriesHeaders(): void
    {
        $grid = $this->createDataGrid();
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

    public function test_defaultFooterCountText_EN(): void
    {
        Localization::selectAppLocale('de_DE');

        $grid = $this->createDataGrid();
        $this->assertSame('Zeige Einträge 1 bis 2, 2 insgesamt.', $grid->getFooterCountText(1, 2, 2));

        Localization::selectAppLocale('en_UK');

        $this->assertSame('Showing entries 1 to 2, 2 total.', $grid->getFooterCountText(1, 2, 2));
    }

    public function test_defaultFooterCountText_DE(): void
    {
        Localization::selectAppLocale('de_DE');
        $grid = $this->createDataGrid();
        $this->assertSame('Zeige Einträge 1 bis 2, 2 insgesamt.', $grid->getFooterCountText(1, 2, 2));
    }

    public function test_customFooterCountText_EN(): void
    {
        Localization::selectAppLocale('en_UK');
        $grid = $this->createDataGrid();
        $grid->setFooterCountText(t('Showing communication types %1$s to %2$s, %3$s total.', '[FROM]', '[TO]', '[TOTAL]'));
        $this->assertSame('Showing communication types 1 to 2, 2 total.', $grid->getFooterCountText(1, 2, 2));
    }

    /**
     * Some cell values are converted to string automatically if they
     * have not been specified as strings. This ensures that they are
     * converted as expected.
     */
    public function test_valueConversions() : void
    {
        $tests = array(
            array(
                // Used to verify #72 has been fixed
                // https://github.com/Mistralys/application-framework/issues/72
                'label' => 'Callable string',
                'value' => 'date',
                'expected' => 'date'
            ),
            array(
                'label' => 'Date value',
                'value' => new DateTime('1975-02-07 19:00:00'),
                'expected' => '<span title="07.02.1975 19:00">07. Feb 1975 19:00</span>'
            ),
            array(
                'label' => 'Integer',
                'value' => 42,
                'expected' => '42'
            ),
            array(
                'label' => 'Callback function',
                'value' => function () : string {
                    return 'foo';
                },
                'expected' => 'foo'
            ),
            array(
                'label' => 'Callback function with type conversion',
                'value' => function () : bool {
                    return false;
                },
                'expected' => 'false'
            )
        );

        $grid = $this->createDataGrid();
        $col = $grid->addColumn('test', 'Test');

        foreach($tests as $test) {
            $entry = $grid->createEntry(array('test' => $test['value']));
            $this->assertSame($test['expected'], $entry->getValueForColumn($col));
        }
    }
}
