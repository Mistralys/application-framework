<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Application\Tags\TagRegistry;
use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

class TagRegistryTest extends TaggingTestCase
{
    public function test_registerKey() : void
    {
        $key = 'test_registry_key';

        $this->assertFalse(TagRegistry::isKeyRegistered($key));

        $tag = $this->createTestRootTag('Test Registry Key');

        TagRegistry::setTagByKey($key, $tag);

        $this->assertTrue(TagRegistry::isKeyRegistered($key));
    }

    public function test_getIDByKey() : void
    {
        $key = 'test_registry_key';

        $tag = $this->createTestRootTag('Test Registry Key');

        TagRegistry::setTagByKey($key, $tag);

        $this->assertSame($tag->getID(), TagRegistry::getTagIDByKey($key));
    }

    public function test_getTagByKey() : void
    {
        $key = 'test_registry_key';

        $tag = $this->createTestRootTag('Test Registry Key');

        TagRegistry::setTagByKey($key, $tag);

        $this->assertSame($tag, TagRegistry::getTagByKey($key));
    }

    public function test_getTagByKeyNotExists() : void
    {
        $this->assertNull(TagRegistry::getTagByKey('not_exists'));
    }
}
