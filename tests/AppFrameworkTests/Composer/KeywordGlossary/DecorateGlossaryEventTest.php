<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\KeywordGlossary;

use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent;
use Application\Composer\KeywordGlossary\GlossarySection;
use Application\Composer\KeywordGlossary\GlossarySectionEntry;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see DecorateGlossaryEvent}.
 * Pure unit tests — no DB, no filesystem dependency.
 */
final class DecorateGlossaryEventTest extends ApplicationTestCase
{
    private function makeSection(string $heading) : GlossarySection
    {
        return new GlossarySection($heading, array('Col'), array(new GlossarySectionEntry(array('Value'))));
    }

    private function makeEvent() : DecorateGlossaryEvent
    {
        return new DecorateGlossaryEvent(DecorateGlossaryEvent::EVENT_NAME);
    }

    public function test_getSections_emptyWhenNoneAdded() : void
    {
        $event = $this->makeEvent();

        $this->assertSame(array(), $event->getSections());
    }

    public function test_addSection_sectionAppearsInGetSections() : void
    {
        $event   = $this->makeEvent();
        $section = $this->makeSection('Test Section');

        $event->addSection($section);

        $sections = $event->getSections();

        $this->assertCount(1, $sections);
        $this->assertSame($section, $sections[0]);
    }

    public function test_addSection_multipleSectionsPreserveOrder() : void
    {
        $event    = $this->makeEvent();
        $sectionA = $this->makeSection('Section A');
        $sectionB = $this->makeSection('Section B');
        $sectionC = $this->makeSection('Section C');

        $event->addSection($sectionA);
        $event->addSection($sectionB);
        $event->addSection($sectionC);

        $sections = $event->getSections();

        $this->assertCount(3, $sections);
        $this->assertSame('Section A', $sections[0]->getHeading());
        $this->assertSame('Section B', $sections[1]->getHeading());
        $this->assertSame('Section C', $sections[2]->getHeading());
    }

    public function test_getName_returnsEventName() : void
    {
        $event = $this->makeEvent();

        $this->assertSame(DecorateGlossaryEvent::EVENT_NAME, $event->getName());
        $this->assertSame('DecorateGlossary', $event->getName());
    }
}
