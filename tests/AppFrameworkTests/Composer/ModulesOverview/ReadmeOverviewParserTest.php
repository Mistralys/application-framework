<?php
/**
 * @package AppFrameworkTests
 * @subpackage Composer
 */

declare(strict_types=1);

namespace AppFrameworkTests\Composer\ModulesOverview;

use Application\Composer\ModulesOverview\ReadmeOverviewParser;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see ReadmeOverviewParser}.
 *
 * @package AppFrameworkTests
 * @subpackage Composer
 */
final class ReadmeOverviewParserTest extends ApplicationTestCase
{
    /**
     * @var string[] Paths of temp files created during tests; cleaned up in tearDown.
     */
    private array $tempFiles = array();

    protected function tearDown() : void
    {
        parent::tearDown();

        foreach($this->tempFiles as $path) {
            if(file_exists($path)) {
                unlink($path);
            }
        }

        $this->tempFiles = array();
    }

    /**
     * Creates a temporary file with the given content and registers it for cleanup.
     */
    private function createTempReadme(string $content) : string
    {
        $path = tempnam(sys_get_temp_dir(), 'readme_parser_test_');

        file_put_contents($path, $content);

        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * AC 1: extractOverview() on a README.md containing ## Overview followed
     * by paragraph text returns the trimmed paragraph text.
     */
    public function test_extractOverview_returnsTextForOverviewSection() : void
    {
        $path = $this->createTempReadme(
            "# Module Title\n\n## Overview\n\nThis is the module overview text.\n"
        );

        $result = ReadmeOverviewParser::extractOverview($path);

        $this->assertSame('This is the module overview text.', $result);
    }

    /**
     * AC 2: extractOverview() on a README.md with ## Overview followed by
     * another ## Heading returns only the text between the two headings.
     */
    public function test_extractOverview_stopsAtNextH2Heading() : void
    {
        $path = $this->createTempReadme(
            "# Module Title\n\n## Overview\n\nBrief description of the module.\n\n## Usage\n\nUsage details go here.\n"
        );

        $result = ReadmeOverviewParser::extractOverview($path);

        $this->assertSame('Brief description of the module.', $result);
    }

    /**
     * AC 3: extractOverview() on a file without a ## Overview section returns null.
     */
    public function test_extractOverview_returnsNullWithoutOverviewSection() : void
    {
        $path = $this->createTempReadme(
            "# Module Title\n\n## Usage\n\nSome content here.\n"
        );

        $result = ReadmeOverviewParser::extractOverview($path);

        $this->assertNull($result);
    }

    /**
     * AC 4: extractOverview() with a path to a non-existent file returns null.
     */
    public function test_extractOverview_returnsNullForNonExistentFile() : void
    {
        $result = ReadmeOverviewParser::extractOverview('/nonexistent/path/to/README.md');

        $this->assertNull($result);
    }

    /**
     * Regex fix validation: a ### sub-heading inside an ## Overview section
     * must NOT stop extraction — only an H2 (##) heading terminates the block.
     *
     * This test validates the lookahead fix from (?=^##|\z) to (?=^##(?!#)|\z).
     * Regressing to the old pattern would make this test fail.
     */
    public function test_extractOverview_doesNotStopAtH3SubHeading() : void
    {
        $path = $this->createTempReadme(
            "# Module Title\n\n## Overview\n\nIntroductory paragraph.\n\n### Details\n\nMore detail text.\n\n## Usage\n\nUsage content.\n"
        );

        $result = ReadmeOverviewParser::extractOverview($path);

        $this->assertNotNull($result, 'H3 inside Overview should not stop extraction.');
        $this->assertStringContainsString('Introductory paragraph.', $result);
        $this->assertStringContainsString('Details', $result);
        $this->assertStringContainsString('More detail text.', $result);
        $this->assertStringNotContainsString('Usage content.', $result);
    }

    /**
     * extractOverview() returns null when the ## Overview section exists but
     * contains only whitespace.
     */
    public function test_extractOverview_returnsNullForEmptyOverviewSection() : void
    {
        $path = $this->createTempReadme(
            "# Module Title\n\n## Overview\n\n   \n\n## Usage\n\nContent.\n"
        );

        $result = ReadmeOverviewParser::extractOverview($path);

        $this->assertNull($result);
    }

    /**
     * extractOverview() returns the full remaining text when ## Overview is the
     * last heading in the file (no subsequent ## heading).
     */
    public function test_extractOverview_returnsTextWhenOverviewIsLastSection() : void
    {
        $path = $this->createTempReadme(
            "# Module Title\n\n## Overview\n\nThis module handles X, Y, and Z.\n"
        );

        $result = ReadmeOverviewParser::extractOverview($path);

        $this->assertSame('This module handles X, Y, and Z.', $result);
    }
}
