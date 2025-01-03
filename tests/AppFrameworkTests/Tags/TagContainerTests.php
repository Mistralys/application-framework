<?php
/**
 * @package Tagging
 * @subpackage Tests
 */

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

/**
 * The test DB collection has a custom tag container class,
 * {@see TestDBTagConnector}, which is used to retrieve records
 * directly of the correct type.
 *
 * @package Tagging
 * @subpackage Tests
 */
final class TagContainerTests extends TaggingTestCase
{
    public function test_getRecordsByTag() : void
    {
        $tag = $this->tagsCollection->createNewTag('Foo1');

        $this->recordCollection->createTestRecord('WithoutTag', 'without');
        $this->recordCollection->createTestRecord('WithTag', 'with1')->getTagManager()->addTag($tag);
        $this->recordCollection->createTestRecord('AlsoWithTag', 'with2')->getTagManager()->addTag($tag);

        $this->assertCount(3, $this->recordCollection->getAll());

        $manager = $this->recordCollection->getTagConnector();
        $records = $manager->getByTag($tag);

        $this->assertCount(2, $records);
        $this->assertTestRecordsContainNames($records, array('WithTag', 'AlsoWithTag'));
    }
}
