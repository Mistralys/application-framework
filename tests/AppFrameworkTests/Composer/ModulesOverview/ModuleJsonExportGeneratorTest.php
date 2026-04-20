<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use Application\Composer\ModulesOverview\ModuleInfo;
use Application\Composer\ModulesOverview\ModuleJsonExportGenerator;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * A test subclass that overrides the hook methods to verify Template Method
 * override behaviour in tests.
 */
final class TestModuleJsonExportGenerator extends ModuleJsonExportGenerator
{
    protected function resolveModuleSource(ModuleInfo $module) : string
    {
        return 'test-source';
    }

    protected function resolveModuleBrief(ModuleInfo $module, string $sourcePath) : string
    {
        return 'Custom brief for ' . $module->getId() . '.';
    }
}

/**
 * Unit tests for the framework {@see ModuleJsonExportGenerator}.
 * Uses temporary fixture directories and synthetic YAML files.
 *
 * @package AppFrameworkTests\Composer\ModulesOverview
 */
final class ModuleJsonExportGeneratorTest extends ApplicationTestCase
{
    private string $tempRoot   = '';
    private string $tempOutput = '';

    protected function setUp() : void
    {
        parent::setUp();

        $this->tempRoot   = sys_get_temp_dir() . '/mje-fw-test-' . getmypid() . '-' . mt_rand(0, 9999);
        $this->tempOutput = $this->tempRoot . '/output.json';
    }

    protected function tearDown() : void
    {
        $this->removeFixtureRoot();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Fixture helpers
    // -------------------------------------------------------------------------

    private function buildFixtureRoot(bool $withBrief = true) : void
    {
        $this->writeRootContextYaml($this->tempRoot);

        $this->writeModuleContextYaml(
            $this->tempRoot . '/fixture-module-alpha',
            'fixture-alpha',
            'Fixture Module Alpha',
            'Alpha fixture module for testing.',
            array('AlphaKeyword')
        );

        if($withBrief)
        {
            file_put_contents(
                $this->tempRoot . '/fixture-module-alpha/README-Brief.md',
                'Brief for fixture-alpha.'
            );
        }
    }

    private function writeRootContextYaml(string $rootDir) : void
    {
        if(!is_dir($rootDir))
        {
            mkdir($rootDir, 0777, true);
        }

        $yaml = "import:\n  - path: \"**/module-context.yaml\"\n";
        file_put_contents($rootDir . '/context.yaml', $yaml);
    }

    private function writeModuleContextYaml(
        string $dirPath,
        string $moduleId,
        string $label,
        string $description,
        array  $keywords = array()
    ) : void
    {
        if(!is_dir($dirPath))
        {
            mkdir($dirPath, 0777, true);
        }

        $keywordLines = '';
        foreach($keywords as $kw)
        {
            $keywordLines .= '    - ' . $kw . "\n";
        }

        $yaml = "moduleMetaData:\n" .
                '  id: "' . $moduleId . '"' . "\n" .
                '  label: "' . $label . '"' . "\n" .
                '  description: "' . $description . '"' . "\n";

        if($keywordLines !== '')
        {
            $yaml .= "  keywords:\n" . $keywordLines;
        }

        file_put_contents($dirPath . '/module-context.yaml', $yaml);
    }

    private function removeFixtureRoot() : void
    {
        if(!is_dir($this->tempRoot))
        {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tempRoot, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach($iterator as $item)
        {
            if($item->isDir())
            {
                rmdir((string)$item);
            }
            else
            {
                unlink((string)$item);
            }
        }

        rmdir($this->tempRoot);
    }

    /**
     * Runs the generator and returns the decoded JSON as an associative array.
     *
     * @param bool               $includeAll
     * @param ModuleJsonExportGenerator|null $generator Optional custom generator instance.
     * @return array<string, mixed>
     */
    private function runGenerator(bool $includeAll = false, ?ModuleJsonExportGenerator $generator = null) : array
    {
        if($generator === null)
        {
            $generator = new ModuleJsonExportGenerator(FolderInfo::factory($this->tempRoot));
        }

        $generator->generate($this->tempOutput, $includeAll);

        $json = FileInfo::factory($this->tempOutput)->getContents();

        $data = json_decode($json, true);

        $this->assertIsArray($data, 'Output file must contain valid JSON.');

        return $data;
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * End-to-end: generate() must produce a valid JSON file containing the
     * expected top-level keys.
     */
    public function test_generate_producesValidJsonWithExpectedKeys() : void
    {
        $this->buildFixtureRoot(true);

        $data = $this->runGenerator();

        $this->assertArrayHasKey('generatedAt',      $data);
        $this->assertArrayHasKey('modules',          $data);
        $this->assertArrayHasKey('glossary',         $data);
        $this->assertArrayHasKey('glossarySections', $data);
        $this->assertIsArray($data['modules']);
        $this->assertIsArray($data['glossary']);
        $this->assertIsArray($data['glossarySections']);
    }

    /**
     * When `$includeAll = false` and the module has no README-Brief.md,
     * the module must be excluded from the output.
     */
    public function test_generate_includeAll_false_excludesModuleWithoutBrief() : void
    {
        $this->buildFixtureRoot(false); // no brief file

        $data = $this->runGenerator(false);

        $this->assertCount(
            0,
            $data['modules'],
            'Module without a brief must be excluded when $includeAll is false.'
        );
    }

    /**
     * When `$includeAll = true` and the module has no README-Brief.md,
     * the module must be included in the output with `brief = null`.
     */
    public function test_generate_includeAll_true_includesModuleWithNullBrief() : void
    {
        $this->buildFixtureRoot(false); // no brief file

        $data = $this->runGenerator(true);

        $this->assertCount(
            1,
            $data['modules'],
            'Module without a brief must be included when $includeAll is true.'
        );

        $this->assertNull(
            $data['modules'][0]['brief'],
            'Module included via $includeAll must have brief = null.'
        );
    }

    /**
     * Hook override: a custom subclass overriding resolveModuleSource() and
     * resolveModuleBrief() must have its return values reflected in the output.
     */
    public function test_generate_hookOverride_customSourceAndBrief() : void
    {
        $this->buildFixtureRoot(false); // no brief on disk — subclass supplies its own

        $generator = new TestModuleJsonExportGenerator(FolderInfo::factory($this->tempRoot));

        $data = $this->runGenerator(true, $generator);

        $this->assertCount(1, $data['modules']);
        $this->assertSame('test-source', $data['modules'][0]['source']);
        $this->assertStringContainsString('Custom brief for', (string)$data['modules'][0]['brief']);
    }

    /**
     * Empty fixture root: no module-context.yaml files → modules and glossary
     * must be empty arrays.
     */
    public function test_generate_emptyFixtureRoot_producesEmptyArrays() : void
    {
        // Create only a root context.yaml with no module subdirs.
        $this->writeRootContextYaml($this->tempRoot);

        $data = $this->runGenerator();

        $this->assertCount(0, $data['modules'],  'modules must be empty for a root with no modules.');
        $this->assertCount(0, $data['glossary'], 'glossary must be empty for a root with no modules.');
    }
}
