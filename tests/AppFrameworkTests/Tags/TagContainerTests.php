<?php
/**
 * @package Application Tests
 * @subpackage Tagging
 */

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

/**
 * The test DB collection has a custom tag container class,
 * {@see TestDBTagContainer}, which is used to retrieve records
 * directly of the correct type.
 *
 * @package Application Tests
 * @subpackage Tagging
 */
final class TagContainerTests extends TaggingTestCase
{
    public function test_getRecordsByTag() : void
    {
        $tag = $this->tagsCollection->createNewTag('Foo1');

        $this->recordCollection->createTestRecord('WithoutTag', 'without');
        $this->recordCollection->createTestRecord('WithTag', 'with1')->getTagger()->addTag($tag);
        $this->recordCollection->createTestRecord('AlsoWithTag', 'with2')->getTagger()->addTag($tag);

        $this->assertCount(3, $this->recordCollection->getAll());

        $manager = $this->recordCollection->getTagContainer();
        $records = $manager->getByTag($tag);

        $this->assertCount(2, $records);
        $this->assertTestRecordsContainNames($records, array('WithTag', 'AlsoWithTag'));
    }
}
