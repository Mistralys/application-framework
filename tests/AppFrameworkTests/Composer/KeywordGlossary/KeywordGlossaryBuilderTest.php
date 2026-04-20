<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\KeywordGlossary;

use Application\Composer\KeywordGlossary\KeywordGlossaryBuilder;
use Application\Composer\ModulesOverview\ModuleInfo;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see KeywordGlossaryBuilder}.
 * Pure unit tests — no DB or filesystem dependency.
 */
final class KeywordGlossaryBuilderTest extends ApplicationTestCase
{
    // -------------------------------------------------------------------------
    // Fixture helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a minimal {@see ModuleInfo} for use in tests.
     *
     * @param string   $id       Module ID.
     * @param string[] $keywords Raw keyword strings (may include context in parentheses).
     * @return ModuleInfo
     */
    private function createModule(string $id, array $keywords = array()) : ModuleInfo
    {
        return new ModuleInfo(
            $id,
            'Label for ' . $id,
            'Description for ' . $id,
            array(),
            'assets/classes/' . $id . '/',
            '.context/modules/' . $id . '/',
            'test/package',
            $keywords
        );
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * Two modules with distinct keywords must produce all keywords,
     * sorted alphabetically by keyword term.
     */
    public function test_build_basicDistinctKeywords() : void
    {
        $modules = array(
            $this->createModule('mod-beta',  array('Zebra (striped animal)')),
            $this->createModule('mod-alpha', array('Apple (fruit)')),
        );

        $entries = (new KeywordGlossaryBuilder($modules))->build();

        $this->assertCount(2, $entries);
        $this->assertSame('Apple', $entries[0]->getKeyword());
        $this->assertSame('Zebra', $entries[1]->getKeyword());
    }

    /**
     * The same keyword appearing in two modules must produce a single entry
     * with both module IDs merged.
     */
    public function test_build_deduplication_mergesModuleIds() : void
    {
        $modules = array(
            $this->createModule('mod-one', array('SharedTerm (shared context)')),
            $this->createModule('mod-two', array('SharedTerm (shared context)')),
        );

        $entries = (new KeywordGlossaryBuilder($modules))->build();

        $this->assertCount(1, $entries);
        $this->assertSame('SharedTerm', $entries[0]->getKeyword());
        $this->assertContains('mod-one', $entries[0]->getModuleIds());
        $this->assertContains('mod-two', $entries[0]->getModuleIds());
    }

    /**
     * The first-seen casing of a keyword must be preserved; later occurrences
     * of the same keyword (case-insensitive) must not overwrite the stored term.
     */
    public function test_build_firstSeenCasingPreserved() : void
    {
        $modules = array(
            $this->createModule('mod-first',  array('CamelCase (a term)')),
            $this->createModule('mod-second', array('camelcase (a term)')),
        );

        $entries = (new KeywordGlossaryBuilder($modules))->build();

        $this->assertCount(1, $entries);
        $this->assertSame('CamelCase', $entries[0]->getKeyword());
    }

    /**
     * When the same keyword appears in two modules with different context
     * strings, the `$onProgress` callback must receive a conflict warning
     * containing the keyword term.
     */
    public function test_build_conflictWarning_emittedViaCallback() : void
    {
        $messages = array();

        $onProgress = static function(string $message) use (&$messages) : void {
            $messages[] = $message;
        };

        $modules = array(
            $this->createModule('mod-a', array('TermX (context alpha)')),
            $this->createModule('mod-b', array('TermX (context beta)')),
        );

        (new KeywordGlossaryBuilder($modules, $onProgress))->build();

        $this->assertNotEmpty($messages, 'A conflict warning must have been emitted.');
        $this->assertStringContainsString('TermX', $messages[0]);
        $this->assertStringContainsString('WARNING', $messages[0]);
    }

    /**
     * When the same keyword appears in two modules with different context
     * strings but no `$onProgress` callback is set, no error must occur.
     */
    public function test_build_conflictWarning_suppressedWithoutCallback() : void
    {
        $modules = array(
            $this->createModule('mod-a', array('TermY (context alpha)')),
            $this->createModule('mod-b', array('TermY (context beta)')),
        );

        // Must not throw an exception.
        $entries = (new KeywordGlossaryBuilder($modules))->build();

        $this->assertCount(1, $entries);
    }

    /**
     * A module with an empty keyword string must be skipped silently;
     * no entry must appear in the result.
     */
    public function test_build_emptyKeyword_skipped() : void
    {
        $modules = array(
            $this->createModule('mod-empty', array('')),
        );

        $entries = (new KeywordGlossaryBuilder($modules))->build();

        $this->assertCount(0, $entries);
    }

    /**
     * Passing an empty module list must return an empty result array.
     */
    public function test_build_emptyModuleList_returnsEmpty() : void
    {
        $entries = (new KeywordGlossaryBuilder(array()))->build();

        $this->assertCount(0, $entries);
    }
}
