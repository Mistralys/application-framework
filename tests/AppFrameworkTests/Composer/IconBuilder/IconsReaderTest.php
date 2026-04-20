<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\IconBuilder;

use Application\Composer\IconBuilder\IconsReader;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see IconsReader}.
 *
 * Tests verify icon loading, ID normalisation, spinner exclusion, sort order,
 * graceful handling of missing files, and IconDefinition property accessors.
 */
final class IconsReaderTest extends ApplicationTestCase
{
    // -------------------------------------------------------------------------
    // Fixtures & helpers
    // -------------------------------------------------------------------------

    /**
     * Path to the framework's own icons.json – used as a real JSON source.
     */
    private string $iconsJsonPath = '';

    /**
     * @var string[]
     */
    private array $tempFiles = array();

    protected function setUp() : void
    {
        parent::setUp();

        $resolved = realpath(
            __DIR__ . '/../../../../src/themes/default/icons.json'
        );

        $this->assertNotFalse(
            $resolved,
            'Fixture icons.json could not be resolved.'
        );

        $this->iconsJsonPath = $resolved;
    }

    protected function tearDown() : void
    {
        foreach($this->tempFiles as $path)
        {
            if(file_exists($path))
            {
                unlink($path);
            }
        }

        $this->tempFiles = array();

        parent::tearDown();
    }

    /**
     * Creates a temporary JSON file with the given data and registers it
     * for cleanup. Returns the absolute path to the created file.
     *
     * @param array<string,mixed> $data
     * @return string
     */
    private function createTempJsonFile(array $data) : string
    {
        $path = tempnam(sys_get_temp_dir(), 'icons_reader_test_') . '.json';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        $this->tempFiles[] = $path;
        return $path;
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * AC#2 — The framework's icons.json contains 214 entries total; the spinner
     * is excluded, so exactly 213 definitions must be returned.
     */
    public function test_readsExpectedIconCount() : void
    {
        $reader = new IconsReader($this->iconsJsonPath);

        $this->assertSame(
            213,
            $reader->countIcons(),
            'Expected 213 icon definitions (214 total minus 1 spinner).'
        );
    }

    /**
     * AC#3 — The spinner icon must be absent from the result set.
     */
    public function test_excludesSpinnerIcons() : void
    {
        $reader = new IconsReader($this->iconsJsonPath);

        foreach($reader->getIcons() as $definition)
        {
            $this->assertNotSame(
                IconsReader::EXCLUDED_ICON_SPINNER,
                $definition->getID(),
                'The spinner icon must be excluded from the definitions list.'
            );
        }
    }

    /**
     * AC#4 — Hyphens (and spaces) in raw JSON keys must be normalised to
     * underscores in the resulting IconDefinition ID.
     *
     * Uses a temporary JSON file so we are not dependent on the real icons.json
     * content changing in the future.
     */
    public function test_normalisesIconIDs() : void
    {
        $tmpPath = $this->createTempJsonFile(array(
            'time-tracker' => array('icon' => 'clock', 'type' => 'far'),
        ));

        $reader = new IconsReader($tmpPath);
        $icons  = $reader->getIcons();

        $this->assertCount(1, $icons, 'Expected exactly one icon definition.');
        $this->assertSame(
            'time_tracker',
            $icons[0]->getID(),
            'Hyphen in raw JSON key must be normalised to an underscore.'
        );
    }

    /**
     * AC#5 — Icons must be sorted alphabetically by their normalised ID.
     */
    public function test_definitionsAreSortedByID() : void
    {
        $reader = new IconsReader($this->iconsJsonPath);
        $icons  = $reader->getIcons();

        $ids = array_map(static fn($def) => $def->getID(), $icons);
        $sorted = $ids;
        sort($sorted);

        $this->assertSame(
            $sorted,
            $ids,
            'Icon definitions must be sorted alphabetically by ID.'
        );
    }

    /**
     * AC#6 — Passing a non-existent path must return an empty array without
     * throwing an exception.
     */
    public function test_returnsEmptyOnMissingFile() : void
    {
        $reader = new IconsReader('/this/path/does/not/exist/icons.json');

        $this->assertSame(
            array(),
            $reader->getIcons(),
            'A missing JSON file must produce an empty icons list, not an exception.'
        );

        $this->assertSame(
            0,
            $reader->countIcons(),
            'countIcons() must return 0 when the JSON file does not exist.'
        );
    }

    /**
     * AC#7 — IconDefinition property accessors must return the values that
     * were supplied in the JSON source.
     */
    public function test_iconDefinitionProperties() : void
    {
        $tmpPath = $this->createTempJsonFile(array(
            'activate' => array('icon' => 'sun', 'type' => 'far'),
        ));

        $reader = new IconsReader($tmpPath);
        $icons  = $reader->getIcons();

        $this->assertCount(1, $icons);

        $def = $icons[0];

        $this->assertSame('activate',  $def->getID(),       'getID() mismatch.');
        $this->assertSame('sun',       $def->getIconName(), 'getIconName() mismatch.');
        $this->assertSame('far',       $def->getIconType(), 'getIconType() mismatch.');
    }
}
