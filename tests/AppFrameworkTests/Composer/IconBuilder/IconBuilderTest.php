<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\IconBuilder;

use Application\Composer\IconBuilder\IconBuilder;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see IconBuilder}.
 *
 * Each test that needs valid PHP/JS target files creates temporary copies in
 * the system temp directory. The {@see tearDown()} method removes them after
 * every test so no artefacts linger between runs.
 */
final class IconBuilderTest extends ApplicationTestCase
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
     * Creates a temporary file with optional initial content and registers it
     * for cleanup. Returns the absolute path to the created file.
     *
     * @param string $content
     * @return string
     */
    private function createTempFile(string $content = '') : string
    {
        $path = tempnam(sys_get_temp_dir(), 'icon_builder_test_');
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;
        return $path;
    }

    /**
     * Returns minimal PHP file content containing the required markers with
     * placeholder content between them.
     *
     * @return string
     */
    private function phpFileWithMarkers() : string
    {
        return implode(PHP_EOL, array(
            '<?php',
            'class TestIcon {',
            '    /* START METHODS */',
            '',
            '    // region: Icon methods',
            '    public function placeholder() : self {}',
            '    // endregion',
            '',
            '    /* END METHODS */',
            '}',
        ));
    }

    /**
     * Returns minimal JS file content containing the required markers with
     * placeholder content between them.
     *
     * @return string
     */
    private function jsFileWithMarkers() : string
    {
        return implode(PHP_EOL, array(
            'var TestIcon = {',
            '    /* START METHODS */',
            '',
            '    // region: Icon methods',
            '    Placeholder:function() {},',
            '    // endregion',
            '',
            '    /* END METHODS */',
            '};',
        ));
    }

    /**
     * Creates a valid IconBuilder pointing at temporary files that each contain
     * the required `START METHODS` / `END METHODS` markers.
     *
     * @return array{0: IconBuilder, 1: string, 2: string}
     *   Returns the builder and the paths to the temp PHP and JS files.
     */
    private function builderWithMarkerFiles() : array
    {
        $phpPath = $this->createTempFile($this->phpFileWithMarkers());
        $jsPath  = $this->createTempFile($this->jsFileWithMarkers());

        return array(
            new IconBuilder($this->iconsJsonPath, $phpPath, $jsPath),
            $phpPath,
            $jsPath,
        );
    }

    // -------------------------------------------------------------------------
    // build() – success path
    // -------------------------------------------------------------------------

    public function test_build_succeedsWithValidFiles() : void
    {
        list($builder) = $this->builderWithMarkerFiles();

        $result = $builder->build();

        $this->assertTrue(
            $result->isValid(),
            'Expected build() to return a valid OperationResult, got: ' . $result->getMessage()
        );
    }

    public function test_build_replacesMarkerContent() : void
    {
        list($builder, $phpPath, $jsPath) = $this->builderWithMarkerFiles();

        $builder->build();

        $phpContent = (string)file_get_contents($phpPath);
        $jsContent  = (string)file_get_contents($jsPath);

        // Placeholder methods must have been replaced.
        $this->assertStringNotContainsString(
            'placeholder()',
            $phpContent,
            'Expected placeholder PHP method to be replaced by the builder.'
        );
        $this->assertStringNotContainsString(
            'Placeholder:function()',
            $jsContent,
            'Expected placeholder JS method to be replaced by the builder.'
        );

        // The markers themselves must still be present.
        $this->assertStringContainsString('/* START METHODS */', $phpContent);
        $this->assertStringContainsString('/* END METHODS */', $phpContent);
        $this->assertStringContainsString('/* START METHODS */', $jsContent);
        $this->assertStringContainsString('/* END METHODS */', $jsContent);
    }

    public function test_build_generatesKnownPhpMethod() : void
    {
        list($builder, $phpPath) = $this->builderWithMarkerFiles();

        $builder->build();

        $phpContent = (string)file_get_contents($phpPath);

        // "add" is a well-known icon present in the framework's icons.json.
        $this->assertStringContainsString(
            "public function add() : self",
            $phpContent
        );
    }

    public function test_build_generatesKnownJsMethod() : void
    {
        list($builder, , $jsPath) = $this->builderWithMarkerFiles();

        $builder->build();

        $jsContent = (string)file_get_contents($jsPath);

        // "add" icon → PascalCase "Add" in JS.
        $this->assertStringContainsString(
            "Add:function()",
            $jsContent
        );
    }

    // -------------------------------------------------------------------------
    // build() – getIcons()
    // -------------------------------------------------------------------------

    public function test_getIcons_returnsIconsReader() : void
    {
        list($builder) = $this->builderWithMarkerFiles();

        $builder->build();

        $this->assertGreaterThan(0, $builder->getIcons()->countIcons());
    }

    // -------------------------------------------------------------------------
    // build() – missing PHP file
    // -------------------------------------------------------------------------

    public function test_build_phpFileMissing_returnsError() : void
    {
        $jsPath = $this->createTempFile($this->jsFileWithMarkers());
        $builder = new IconBuilder(
            $this->iconsJsonPath,
            '/nonexistent/path/Icon.php',
            $jsPath
        );

        $result = $builder->build();

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_PHP_ICON_FILE_NOT_FOUND, $result->getCode());
    }

    // -------------------------------------------------------------------------
    // build() – missing JS file
    // -------------------------------------------------------------------------

    public function test_build_jsFileMissing_returnsError() : void
    {
        $phpPath = $this->createTempFile($this->phpFileWithMarkers());
        $builder = new IconBuilder(
            $this->iconsJsonPath,
            $phpPath,
            '/nonexistent/path/icon.js'
        );

        $result = $builder->build();

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_JS_ICON_FILE_NOT_FOUND, $result->getCode());
    }

    // -------------------------------------------------------------------------
    // build() – missing markers
    // -------------------------------------------------------------------------

    public function test_build_phpStartMarkerMissing_returnsError() : void
    {
        $phpPath = $this->createTempFile('<?php class TestIcon { public function foo() {} }');
        $jsPath  = $this->createTempFile($this->jsFileWithMarkers());
        $builder = new IconBuilder($this->iconsJsonPath, $phpPath, $jsPath);

        $result = $builder->build();

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_START_MARKER_NOT_FOUND, $result->getCode());
    }

    public function test_build_jsStartMarkerMissing_returnsError() : void
    {
        $phpPath = $this->createTempFile($this->phpFileWithMarkers());
        $jsPath  = $this->createTempFile('var TestIcon = { Foo:function() {} };');
        $builder = new IconBuilder($this->iconsJsonPath, $phpPath, $jsPath);

        $result = $builder->build();

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_START_MARKER_NOT_FOUND, $result->getCode());
    }

    public function test_build_phpEndMarkerMissing_returnsError() : void
    {
        $phpPath = $this->createTempFile('<?php class TestIcon { /* START METHODS */ public function foo() {} }');
        $jsPath  = $this->createTempFile($this->jsFileWithMarkers());
        $builder = new IconBuilder($this->iconsJsonPath, $phpPath, $jsPath);

        $result = $builder->build();

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_END_MARKER_NOT_FOUND, $result->getCode());
    }

    public function test_build_jsEndMarkerMissing_returnsError() : void
    {
        $phpPath = $this->createTempFile($this->phpFileWithMarkers());
        $jsPath  = $this->createTempFile('var TestIcon = { /* START METHODS */ Foo:function() {} };');
        $builder = new IconBuilder($this->iconsJsonPath, $phpPath, $jsPath);

        $result = $builder->build();

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_END_MARKER_NOT_FOUND, $result->getCode());
    }

    // -------------------------------------------------------------------------
    // build() – write failure
    // -------------------------------------------------------------------------

    public function test_build_writeFailure_returnsError() : void
    {
        list($builder, $phpPath) = $this->builderWithMarkerFiles();

        // Make the PHP target file read-only to simulate a write failure.
        chmod($phpPath, 0444);

        $result = $builder->build();

        // Restore permissions so tearDown can delete the file.
        chmod($phpPath, 0644);

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_WRITE_FAILED, $result->getCode());
    }

    // -------------------------------------------------------------------------
    // build() – read failure
    // -------------------------------------------------------------------------

    public function test_build_readFailure_returnsError() : void
    {
        list($builder, $phpPath) = $this->builderWithMarkerFiles();

        // Make the PHP target file unreadable to simulate a read failure.
        chmod($phpPath, 0000);

        $result = $builder->build();

        // Restore permissions so tearDown can delete the file.
        chmod($phpPath, 0644);

        $this->assertFalse($result->isValid());
        $this->assertSame(IconBuilder::ERROR_READ_FAILED, $result->getCode());
    }
}
