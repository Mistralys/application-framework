<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use DBHelper;
use TestDriver\TestDBRecords\TestDBCollection;

abstract class TaggingTestCase extends ApplicationTestCase
{
    // region Configuration

    protected TagCollection $tagsCollection;
    protected TestDBCollection $recordCollection;

    /**
     * @var string[]
     */
    private array $tableCleanup = array(
        TagCollection::TABLE_NAME,
        TagCollection::TABLE_REGISTRY,
        TestDBCollection::TABLE_NAME
    );

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        $this->cleanUpTables($this->tableCleanup);

        $this->tagsCollection = AppFactory::createTags();
        $this->recordCollection = TestDBCollection::getInstance();
    }

    // endregion

    // region Creating test records

    public function createTestRootTag(?string $label='') : TagRecord
    {
        if(empty($label)) {
            $label = 'Root tag '.$this->getTestCounter('tags');
        }

        $tag = $this->tagsCollection->createNewTag($label);

        $this->assertTrue($tag->isRootTag());

        return $tag;
    }

    // endregion
}
