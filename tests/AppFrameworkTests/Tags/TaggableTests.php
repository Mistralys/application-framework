<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

final class TaggableTests extends TaggingTestCase
{
    public function test_addTag() : void
    {
        $record = $this->recordCollection->createTestRecord('Foo', 'foo');
        $manager = $record->getTagger();

        $this->assertSame(0, $manager->countTags());

        $tag = $this->tagsCollection->createNewTag('FooTag');

        $manager->addTag($tag);

        $this->assertSame(1, $manager->countTags());
        $this->assertCount(1, $manager->getAll());
        $this->assertContains($tag, $manager->getAll());
    }

    public function test_hasTag() : void
    {
        $record = $this->recordCollection->createTestRecord('Foo', 'foo');
        $manager = $record->getTagger();

        $tag = $this->tagsCollection->createNewTag('FooTag');
        $manager->addTag($tag);

        $this->assertTrue($manager->hasTag($tag));
    }

    public function test_removeTag() : void
    {
        $record = $this->recordCollection->createTestRecord('Foo', 'foo');
        $manager = $record->getTagger();

        $tag = $this->tagsCollection->createNewTag('FooTag');
        $manager->addTag($tag);

        $this->assertTrue($manager->hasTag($tag));

        $manager->removeTag($tag);

        $this->assertFalse($manager->hasTag($tag));
    }

    public function test_removeAll() : void
    {
        $record = $this->recordCollection->createTestRecord('Foo', 'foo');
        $manager = $record->getTagger();

        $manager->addTag($this->tagsCollection->createNewTag('FooTag'));
        $manager->addTag($this->tagsCollection->createNewTag('BarTag'));

        $this->assertSame(2, $manager->countTags());

        $manager->removeAll();

        $this->assertSame(0, $manager->countTags());
    }
}
