<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Application\Tags\TagRegistry;
use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

class TagRegistryTests extends TaggingTestCase
{
    public function test_registerKey() : void
    {
        $key = 'test_registry_key';

        $this->assertFalse(TagRegistry::isKeyRegistered($key));

        $tag = TagRegistry::registerKey($key, 'Test Registry Key');

        $this->assertTrue(TagRegistry::isKeyRegistered($key));
        $this->assertTrue($tag->isRootTag());
    }

    public function test_getIDByKey() : void
    {
        $key = 'test_registry_key';
        $tag = TagRegistry::registerKey($key, 'Test Registry Key');

        $this->assertSame($tag->getID(), TagRegistry::getTagIDByKey($key));
    }

    public function test_getTagByKey() : void
    {
        $key = 'test_registry_key';
        $tag = TagRegistry::registerKey($key, 'Test Registry Key');

        $this->assertSame($tag, TagRegistry::getTagByKey($key));
    }

    public function test_getTagByKeyNotExists() : void
    {
        $this->expectExceptionCode(TagRegistry::ERROR_KEY_NOT_REGISTERED);

        TagRegistry::getTagByKey('not_exists');
    }
}
