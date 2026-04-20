<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\IconBuilder;

use Application\Composer\IconBuilder\AbstractLanguageRenderer;
use Application\Composer\IconBuilder\IconDefinition;
use Application\Composer\IconBuilder\IconsReader;
use Application\Composer\IconBuilder\PHPRenderer;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see AbstractLanguageRenderer}.
 *
 * Uses a {@see TestableLanguageRenderer} subclass to expose the protected
 * `toPascalCase()` method, and a real concrete renderer ({@see PHPRenderer})
 * to verify the `render()` output.
 */

// ---------------------------------------------------------------------------
// Test-double: exposes the protected toPascalCase() method for direct testing
// ---------------------------------------------------------------------------

// Lives at file scope because PHP does not support inner/nested classes.

/**
 * Concrete subclass of {@see AbstractLanguageRenderer} used exclusively in
 * tests. It exposes the protected `toPascalCase()` method as a public method
 * so that tests can assert its behaviour without routing through a full render.
 */
final class TestableLanguageRenderer extends AbstractLanguageRenderer
{
    /**
     * Minimal concrete implementation – renders a single-line placeholder so
     * that `render()` can be exercised end-to-end.
     *
     * @param IconDefinition $icon
     * @return string
     */
    protected function renderMethod(IconDefinition $icon) : string
    {
        $methodName = $this->toPascalCase($icon->getID());

        return '    // method: ' . $methodName . PHP_EOL;
    }

    /**
     * Exposes the protected `toPascalCase()` for direct unit testing.
     *
     * @param string $id
     * @return string
     */
    public function exposeToPascalCase(string $id) : string
    {
        return $this->toPascalCase($id);
    }
}

// ---------------------------------------------------------------------------
// Test case
// ---------------------------------------------------------------------------

final class AbstractLanguageRendererTest extends ApplicationTestCase
{
    // -------------------------------------------------------------------------
    // Fixtures & helpers
    // -------------------------------------------------------------------------

    /**
     * Absolute path to the framework's own icons.json – used as a real JSON
     * source when a populated IconsReader is required.
     */
    private string $iconsJsonPath = '';

    /**
     * Temporary files created by {@see self::readerFromArray()}. Tracked so
     * that {@see self::tearDown()} can remove them after every test.
     *
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
     * Creates an {@see IconsReader} backed by a temporary JSON file whose
     * content is built from the supplied map of id → ['icon' => ..., 'type' => ...].
     * The temporary file is tracked in {@see self::$tempFiles} and removed in
     * {@see self::tearDown()}.
     *
     * @param array<string, array{icon: string, type: string}> $icons
     * @return IconsReader
     */
    private function readerFromArray(array $icons) : IconsReader
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'ab_renderer_test_') . '.json';
        file_put_contents($tmpPath, (string)json_encode($icons));
        $this->tempFiles[] = $tmpPath;
        return new IconsReader($tmpPath);
    }

    // -------------------------------------------------------------------------
    // AC-2 / AC-3  toPascalCase conversion
    // -------------------------------------------------------------------------

    /**
     * Verifies that underscore-separated IDs are converted to PascalCase.
     *
     * Examples exercised:
     *   - `time_tracker`        → `TimeTracker`
     *   - `attention_required`  → `AttentionRequired`
     *   - `actioncode`          → `Actioncode`   (single word, first letter upcased)
     *   - `a_b_c`               → `ABC`          (single-char segments)
     */
    public function test_toPascalCase() : void
    {
        $renderer = new TestableLanguageRenderer(
            new IconsReader($this->iconsJsonPath)
        );

        $this->assertSame('TimeTracker', $renderer->exposeToPascalCase('time_tracker'),
            'Underscore-separated ID must become PascalCase.'
        );

        $this->assertSame('AttentionRequired', $renderer->exposeToPascalCase('attention_required'),
            'Multi-word underscore ID must become PascalCase.'
        );

        $this->assertSame('Actioncode', $renderer->exposeToPascalCase('actioncode'),
            'Single-word ID must have only its first letter uppercased.'
        );

        $this->assertSame('ABC', $renderer->exposeToPascalCase('a_b_c'),
            'Single-character segments must each be uppercased.'
        );
    }

    // -------------------------------------------------------------------------
    // AC-4  render() produces non-empty output
    // -------------------------------------------------------------------------

    /**
     * Verifies that `render()` on a concrete renderer returns a non-empty
     * string when at least one icon definition is present.
     */
    public function test_renderProducesOutput() : void
    {
        $reader = $this->readerFromArray(array(
            'time_tracker' => array('icon' => 'clock', 'type' => 'far'),
            'add'          => array('icon' => 'plus',  'type' => ''),
        ));

        // Use the real PHPRenderer as the concrete subclass under test.
        $renderer = new PHPRenderer($reader);

        $output = $renderer->render();

        $this->assertNotEmpty($output,
            'render() must return a non-empty string when icons are present.'
        );
    }

    // -------------------------------------------------------------------------
    // AC-5  render() output contains PascalCase-derived method names
    // -------------------------------------------------------------------------

    /**
     * Verifies that the rendered output contains method names derived from
     * PascalCase-converted icon IDs.
     *
     * The {@see TestableLanguageRenderer} writes `// method: {PascalCase}` for
     * each icon, making it straightforward to assert the exact method names
     * without coupling to a language-specific syntax.
     */
    public function test_renderContainsMethodNames() : void
    {
        $reader = $this->readerFromArray(array(
            'time_tracker' => array('icon' => 'clock', 'type' => 'far'),
            'add'          => array('icon' => 'plus',  'type' => ''),
        ));

        $renderer = new TestableLanguageRenderer($reader);
        $output   = $renderer->render();

        $this->assertStringContainsString('TimeTracker', $output,
            'Rendered output must contain PascalCase method name "TimeTracker" for ID "time_tracker".'
        );

        $this->assertStringContainsString('Add', $output,
            'Rendered output must contain PascalCase method name "Add" for ID "add".'
        );
    }
}
