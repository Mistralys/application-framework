<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\KeywordGlossary;

use Application\Composer\KeywordGlossary\GlossarySection;
use Application\Composer\KeywordGlossary\GlossarySectionEntry;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see GlossarySection} and {@see GlossarySectionEntry}.
 * Pure unit tests — no DB, no filesystem dependency.
 */
final class GlossarySectionTest extends ApplicationTestCase
{
    // -------------------------------------------------------------------------
    // GlossarySectionEntry tests
    // -------------------------------------------------------------------------

    public function test_sectionEntry_getValues_returnsSuppliedValues() : void
    {
        $entry = new GlossarySectionEntry(array('Alpha', 'Beta', 'Gamma'));

        $this->assertSame(array('Alpha', 'Beta', 'Gamma'), $entry->getValues());
    }

    public function test_sectionEntry_getValues_emptyArrayWhenEmpty() : void
    {
        $entry = new GlossarySectionEntry(array());

        $this->assertSame(array(), $entry->getValues());
    }

    // -------------------------------------------------------------------------
    // GlossarySection tests
    // -------------------------------------------------------------------------

    public function test_section_getHeading_returnsSuppliedHeading() : void
    {
        $section = new GlossarySection('My Heading', array('Col A'), array());

        $this->assertSame('My Heading', $section->getHeading());
    }

    public function test_section_getColumnHeaders_returnsSuppliedHeaders() : void
    {
        $section = new GlossarySection('Heading', array('Header 1', 'Header 2'), array());

        $this->assertSame(array('Header 1', 'Header 2'), $section->getColumnHeaders());
    }

    public function test_section_getColumnHeaders_emptyArrayWhenNone() : void
    {
        $section = new GlossarySection('Heading', array(), array());

        $this->assertSame(array(), $section->getColumnHeaders());
    }

    public function test_section_getEntries_returnsAllEntries() : void
    {
        $entryA  = new GlossarySectionEntry(array('A1', 'A2'));
        $entryB  = new GlossarySectionEntry(array('B1', 'B2'));
        $section = new GlossarySection('Section', array('C1', 'C2'), array($entryA, $entryB));

        $entries = $section->getEntries();

        $this->assertCount(2, $entries);
        $this->assertSame(array('A1', 'A2'), $entries[0]->getValues());
        $this->assertSame(array('B1', 'B2'), $entries[1]->getValues());
    }

    public function test_section_getEntries_emptyArrayWhenNoEntries() : void
    {
        $section = new GlossarySection('Empty Section', array('Col'), array());

        $this->assertSame(array(), $section->getEntries());
    }
}
