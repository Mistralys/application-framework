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

    private function writeRootContextYaml(string $rootDir, array $projectExportDocs = array()) : void
    {
        if(!is_dir($rootDir))
        {
            mkdir($rootDir, 0777, true);
        }

        $yaml = "import:\n  - path: \"**/module-context.yaml\"\n";

        if(!empty($projectExportDocs))
        {
            $yaml .= "projectMetaData:\n  exportDocs:\n";
            foreach($projectExportDocs as $docPath)
            {
                $yaml .= '    - ' . $docPath . "\n";
            }
        }

        file_put_contents($rootDir . '/context.yaml', $yaml);
    }

    private function writeModuleContextYaml(
        string $dirPath,
        string $moduleId,
        string $label,
        string $description,
        array  $keywords = array(),
        array  $exportDocs = array()
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

        $exportDocLines = '';
        foreach($exportDocs as $doc)
        {
            $exportDocLines .= '    - ' . $doc . "\n";
        }

        $yaml = "moduleMetaData:\n" .
                '  id: "' . $moduleId . '"' . "\n" .
                '  label: "' . $label . '"' . "\n" .
                '  description: "' . $description . '"' . "\n";

        if($keywordLines !== '')
        {
            $yaml .= "  keywords:\n" . $keywordLines;
        }

        if($exportDocLines !== '')
        {
            $yaml .= "  exportDocs:\n" . $exportDocLines;
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

    /**
     * When a module declares an exportDocs entry pointing to a valid .md file,
     * additionalDocs in the JSON output must contain one entry with the
     * correct fileName and content.
     */
    public function test_generate_exportDocs_includesAdditionalDocsInOutput() : void
    {
        $moduleDir  = $this->tempRoot . '/fixture-module-exportdocs';
        $docContent = '# Service Reference' . "\n\nThis is the service reference document.\n";

        $this->writeRootContextYaml($this->tempRoot);
        $this->writeModuleContextYaml(
            $moduleDir,
            'fixture-exportdocs',
            'Fixture ExportDocs Module',
            'Module for exportDocs test.',
            array(),
            array('Docs/service-reference.md')
        );

        mkdir($moduleDir . '/Docs', 0777, true);
        file_put_contents($moduleDir . '/Docs/service-reference.md', $docContent);
        file_put_contents($moduleDir . '/README-Brief.md', 'Brief for fixture-exportdocs.');

        $data = $this->runGenerator(true);

        $this->assertCount(1, $data['modules']);
        $module = $data['modules'][0];

        $this->assertArrayHasKey('additionalDocs', $module);
        $this->assertIsArray($module['additionalDocs']);
        $this->assertCount(1, $module['additionalDocs']);

        $doc = $module['additionalDocs'][0];
        $this->assertSame('service-reference.md', $doc['fileName']);
        $this->assertSame($docContent, $doc['content']);
    }

    /**
     * When a module declares an exportDocs entry pointing to a non-existent file,
     * additionalDocs must be an empty array (the missing file is skipped gracefully).
     */
    public function test_generate_exportDocs_missingFile_producesEmptyArray() : void
    {
        $moduleDir = $this->tempRoot . '/fixture-module-missingdoc';

        $this->writeRootContextYaml($this->tempRoot);
        $this->writeModuleContextYaml(
            $moduleDir,
            'fixture-missingdoc',
            'Fixture Missing Doc Module',
            'Module for missing exportDocs test.',
            array(),
            array('Docs/does-not-exist.md')
        );
        file_put_contents($moduleDir . '/README-Brief.md', 'Brief for fixture-missingdoc.');

        $data = $this->runGenerator(true);

        $this->assertCount(1, $data['modules']);
        $module = $data['modules'][0];

        $this->assertArrayHasKey('additionalDocs', $module);
        $this->assertIsArray($module['additionalDocs']);
        $this->assertCount(0, $module['additionalDocs'], 'Missing exportDocs file must be skipped gracefully.');
    }

    /**
     * When a module declares an exportDocs entry with a non-.md extension,
     * ModuleInfoParser must filter it out so that getExportDocs() returns
     * an empty array and additionalDocs is empty.
     */
    public function test_generate_exportDocs_nonMarkdownExtension_skippedByParser() : void
    {
        $moduleDir = $this->tempRoot . '/fixture-module-nonmd';

        $this->writeRootContextYaml($this->tempRoot);
        $this->writeModuleContextYaml(
            $moduleDir,
            'fixture-nonmd',
            'Fixture Non-MD Module',
            'Module for non-markdown exportDocs test.',
            array(),
            array('Docs/guide.txt')
        );
        file_put_contents($moduleDir . '/README-Brief.md', 'Brief for fixture-nonmd.');
        mkdir($moduleDir . '/Docs', 0777, true);
        file_put_contents($moduleDir . '/Docs/guide.txt', 'This is a text file.');

        $data = $this->runGenerator(true);

        $this->assertCount(1, $data['modules']);
        $module = $data['modules'][0];

        $this->assertArrayHasKey('additionalDocs', $module);
        $this->assertIsArray($module['additionalDocs']);
        $this->assertCount(0, $module['additionalDocs'], 'Non-.md exportDocs entries must be filtered by the parser.');
    }

    /**
     * When a module declares no exportDocs key, additionalDocs must be an empty array.
     */
    public function test_generate_noExportDocs_producesEmptyArray() : void
    {
        $this->buildFixtureRoot(true); // uses writeModuleContextYaml with no exportDocs

        $data = $this->runGenerator();

        $this->assertCount(1, $data['modules']);
        $module = $data['modules'][0];

        $this->assertArrayHasKey('additionalDocs', $module);
        $this->assertIsArray($module['additionalDocs']);
        $this->assertCount(0, $module['additionalDocs'], 'Module with no exportDocs must have an empty additionalDocs array.');
    }

    /**
     * Security: the containment guard must block a path that resolves into a
     * sibling directory whose name is prefixed by the module directory name.
     *
     * Without a trailing '/' on $sourceBase, str_starts_with() would incorrectly
     * allow paths like /modules/fixture-module-foobar/secret.md through the guard
     * for a module whose directory is /modules/fixture-module-foo, because
     * "fixture-module-foobar" starts with "fixture-module-foo".
     */
    public function test_generate_exportDocs_siblingDirectoryBoundaryCollision_isBlocked() : void
    {
        $moduleFooDir    = $this->tempRoot . '/fixture-module-foo';
        $moduleFoobarDir = $this->tempRoot . '/fixture-module-foobar';

        $this->writeRootContextYaml($this->tempRoot);
        $this->writeModuleContextYaml(
            $moduleFooDir,
            'fixture-foo',
            'Fixture Foo Module',
            'Module for sibling boundary collision test.',
            array(),
            array('../fixture-module-foobar/secret.md')
        );
        file_put_contents($moduleFooDir . '/README-Brief.md', 'Brief for fixture-foo.');

        // Sibling directory with a secret file but no module-context.yaml.
        mkdir($moduleFoobarDir, 0777, true);
        file_put_contents($moduleFoobarDir . '/secret.md', 'SECRET CONTENT');

        $data = $this->runGenerator(true);

        $this->assertCount(1, $data['modules'], 'Only the foo module should appear in output.');
        $module = $data['modules'][0];

        $this->assertArrayHasKey('additionalDocs', $module);
        $this->assertIsArray($module['additionalDocs']);
        $this->assertCount(
            0,
            $module['additionalDocs'],
            'Containment guard must block traversal into a sibling directory whose name is prefixed by the module directory name.'
        );
    }

    // -------------------------------------------------------------------------
    // projectDocs tests
    // -------------------------------------------------------------------------

    /**
     * When no `projectMetaData` section is present in `context.yaml`,
     * the JSON output must contain a `projectDocs` key with an empty array.
     *
     * @covers AC-03
     */
    public function test_generate_projectDocs_keyAlwaysPresent() : void
    {
        $this->buildFixtureRoot(true); // uses writeRootContextYaml with no projectExportDocs

        $data = $this->runGenerator();

        $this->assertArrayHasKey('projectDocs', $data, 'projectDocs key must always be present in JSON output.');
        $this->assertIsArray($data['projectDocs']);
        $this->assertCount(0, $data['projectDocs'], 'projectDocs must be an empty array when no projectMetaData is declared.');
    }

    /**
     * When `context.yaml` declares a valid project doc and the file exists,
     * `projectDocs` must contain one entry with the correct fileName and content.
     *
     * @covers AC-01, AC-04
     */
    public function test_generate_projectDocs_includesConfiguredDocs() : void
    {
        $docContent = "# Platform Module Map\n\nThis document describes the module map.\n";

        mkdir($this->tempRoot . '/docs/platform', 0777, true);
        file_put_contents($this->tempRoot . '/docs/platform/test-doc.md', $docContent);

        $this->writeRootContextYaml($this->tempRoot, array('docs/platform/test-doc.md'));

        $this->writeModuleContextYaml(
            $this->tempRoot . '/fixture-module-alpha',
            'fixture-alpha',
            'Fixture Module Alpha',
            'Alpha fixture module for testing.',
            array('AlphaKeyword')
        );
        file_put_contents($this->tempRoot . '/fixture-module-alpha/README-Brief.md', 'Brief for fixture-alpha.');

        $data = $this->runGenerator();

        $this->assertArrayHasKey('projectDocs', $data);
        $this->assertIsArray($data['projectDocs']);
        $this->assertCount(1, $data['projectDocs'], 'projectDocs must contain one entry for the declared doc.');

        $doc = $data['projectDocs'][0];
        $this->assertSame('test-doc.md', $doc['fileName']);
        $this->assertSame($docContent, $doc['content']);
    }

    /**
     * When a declared project doc path points to a non-existent file,
     * `projectDocs` must be an empty array (the missing file is skipped gracefully).
     *
     * @covers AC-06
     */
    public function test_generate_projectDocs_missingFile_skippedGracefully() : void
    {
        $this->writeRootContextYaml($this->tempRoot, array('docs/platform/does-not-exist.md'));

        $this->writeModuleContextYaml(
            $this->tempRoot . '/fixture-module-alpha',
            'fixture-alpha',
            'Fixture Module Alpha',
            'Alpha fixture module for testing.',
            array('AlphaKeyword')
        );
        file_put_contents($this->tempRoot . '/fixture-module-alpha/README-Brief.md', 'Brief for fixture-alpha.');

        $data = $this->runGenerator();

        $this->assertArrayHasKey('projectDocs', $data);
        $this->assertIsArray($data['projectDocs']);
        $this->assertCount(0, $data['projectDocs'], 'Missing project doc file must be skipped gracefully.');
    }

    /**
     * Security: the containment guard must block a project doc path that
     * resolves to a file outside the project root directory.
     *
     * @covers AC-05
     */
    public function test_generate_projectDocs_pathTraversal_blocked() : void
    {
        // Create a file outside the temp root.
        $outsideDir  = sys_get_temp_dir() . '/mje-fw-outside-' . getmypid() . '-' . mt_rand(0, 9999);
        mkdir($outsideDir, 0777, true);
        file_put_contents($outsideDir . '/outside.md', 'OUTSIDE CONTENT');

        $this->writeRootContextYaml($this->tempRoot, array('../../' . basename($outsideDir) . '/outside.md'));

        $this->writeModuleContextYaml(
            $this->tempRoot . '/fixture-module-alpha',
            'fixture-alpha',
            'Fixture Module Alpha',
            'Alpha fixture module for testing.',
            array('AlphaKeyword')
        );
        file_put_contents($this->tempRoot . '/fixture-module-alpha/README-Brief.md', 'Brief for fixture-alpha.');

        $data = $this->runGenerator();

        // Clean up the outside dir.
        unlink($outsideDir . '/outside.md');
        rmdir($outsideDir);

        $this->assertArrayHasKey('projectDocs', $data);
        $this->assertIsArray($data['projectDocs']);
        $this->assertCount(0, $data['projectDocs'], 'Containment guard must block project doc paths that resolve outside the project root.');
    }

    /**
     * When a declared project doc path has a non-.md extension, it must be
     * filtered during YAML parsing and `projectDocs` must be an empty array.
     *
     * @covers AC-02
     */
    public function test_generate_projectDocs_nonMarkdown_filtered() : void
    {
        mkdir($this->tempRoot . '/docs', 0777, true);
        file_put_contents($this->tempRoot . '/docs/guide.txt', 'This is a text file.');

        $this->writeRootContextYaml($this->tempRoot, array('docs/guide.txt'));

        $this->writeModuleContextYaml(
            $this->tempRoot . '/fixture-module-alpha',
            'fixture-alpha',
            'Fixture Module Alpha',
            'Alpha fixture module for testing.',
            array('AlphaKeyword')
        );
        file_put_contents($this->tempRoot . '/fixture-module-alpha/README-Brief.md', 'Brief for fixture-alpha.');

        $data = $this->runGenerator();

        $this->assertArrayHasKey('projectDocs', $data);
        $this->assertIsArray($data['projectDocs']);
        $this->assertCount(0, $data['projectDocs'], 'Non-.md project doc entries must be filtered during YAML parsing.');
    }
}
